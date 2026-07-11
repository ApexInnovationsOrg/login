<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Models\System;
use App\Models\User;
use App\Saml\RoutingOperator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\Support\SamlResponseFactory;
use Tests\TestCase;

class AttributeRoutingLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private SamlClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
            'saml.replay_store' => 'array',
        ]);

        $this->client = SamlClient::factory()->create([
            'slug' => 'acme',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
            'owner_id' => 933,
            'department_id' => null,
            'jit_enabled' => true,
        ]);
    }

    private function acs(array $responseOverrides = []): TestResponse
    {
        return $this->post('/saml/acme/acs', [
            'SAMLResponse' => SamlResponseFactory::make($responseOverrides),
        ]);
    }

    private function departmentRule(SamlClient $client, string $departmentName, string $value, string $attribute = 'department'): SamlDepartmentRule
    {
        return SamlDepartmentRule::factory()->create([
            'saml_client_id' => $client->id,
            'attribute' => $attribute,
            'operator' => RoutingOperator::Contains,
            'value' => $value,
            'department_name' => $departmentName,
        ]);
    }

    public function test_jit_user_is_routed_to_named_department(): void
    {
        $dept = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'ICU Nursing']);
        $this->departmentRule($this->client, 'ICU Nursing', 'icu');

        // Routed JIT users still have CredentialID = 0, so they land on the
        // finish flow (with their department pre-resolved) — same as clients
        // with a static default department today.
        $this->acs(['attributes' => ['email' => 'new@acme.test', 'firstName' => 'N', 'lastName' => 'U', 'department' => 'Medical ICU'], 'nameId' => 'new@acme.test'])
            ->assertRedirect('/finishAccountCreation');

        $this->assertDatabaseHas('Users', ['Login' => 'new@acme.test', 'DepartmentID' => $dept->ID]);
    }

    public function test_jit_user_with_no_matching_rule_gets_client_default_department(): void
    {
        Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'ICU Nursing']);
        $this->departmentRule($this->client, 'ICU Nursing', 'icu');

        $this->acs(['attributes' => ['email' => 'new@acme.test', 'firstName' => 'N', 'lastName' => 'U', 'department' => 'Radiology'], 'nameId' => 'new@acme.test'])
            ->assertRedirect('/finishAccountCreation');

        // Org-owned client, rule set present but non-matching: department-less
        // placement (org still resolves from ownership) -> DepartmentID 0.
        $this->assertDatabaseHas('Users', ['Login' => 'new@acme.test', 'DepartmentID' => 0]);
    }

    public function test_existing_user_is_moved_to_routed_department(): void
    {
        $deptA = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'Old Wing']);
        $deptB = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'ICU Nursing']);
        $this->departmentRule($this->client, 'ICU Nursing', 'icu');

        $user = User::factory()->create([
            'Login' => 'mover@acme.test',
            'DepartmentID' => $deptA->ID,
            'CredentialID' => 1,
        ]);

        Log::spy();

        $response = $this->acs(['nameId' => 'mover@acme.test', 'attributes' => [
            'email' => 'mover@acme.test', 'firstName' => 'Mover', 'lastName' => 'User', 'department' => 'Medical ICU',
        ]]);

        $response->assertRedirect('https://www.apexinnovations.com/MyCurriculum.php');
        $this->assertDatabaseHas('Users', ['Login' => 'mover@acme.test', 'DepartmentID' => $deptB->ID]);

        Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context) use ($user, $deptA, $deptB) {
            return $message === 'SAML routed user to department'
                && ($context['client'] ?? null) === 'acme'
                && ($context['user_id'] ?? null) === $user->ID
                && ($context['from'] ?? null) === $deptA->ID
                && ($context['to'] ?? null) === $deptB->ID;
        })->once();
    }

    public function test_existing_user_untouched_on_no_rule_match(): void
    {
        $deptA = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'Old Wing']);
        Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'ICU Nursing']);
        $this->departmentRule($this->client, 'ICU Nursing', 'icu');

        User::factory()->create([
            'Login' => 'stays@acme.test',
            'DepartmentID' => $deptA->ID,
            'CredentialID' => 1,
        ]);

        Log::spy();

        $this->acs(['nameId' => 'stays@acme.test', 'attributes' => [
            'email' => 'stays@acme.test', 'firstName' => 'Stays', 'lastName' => 'User', 'department' => 'Radiology',
        ]]);

        $this->assertDatabaseHas('Users', ['Login' => 'stays@acme.test', 'DepartmentID' => $deptA->ID]);
        Log::shouldNotHaveReceived('info', ['SAML routed user to department', \Mockery::any()]);
    }

    public function test_existing_user_never_demoted_when_rule_matches_but_department_missing(): void
    {
        $deptA = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'Old Wing']);
        // Rule matches the attribute but names a department that does not
        // exist anywhere -> placement resolves department_id null, must not touch the user.
        $this->departmentRule($this->client, 'Nonexistent Department', 'icu');

        User::factory()->create([
            'Login' => 'safe@acme.test',
            'DepartmentID' => $deptA->ID,
            'CredentialID' => 1,
        ]);

        Log::spy();

        $this->acs(['nameId' => 'safe@acme.test', 'attributes' => [
            'email' => 'safe@acme.test', 'firstName' => 'Safe', 'lastName' => 'User', 'department' => 'Medical ICU',
        ]]);

        $this->assertDatabaseHas('Users', ['Login' => 'safe@acme.test', 'DepartmentID' => $deptA->ID]);
        Log::shouldNotHaveReceived('info', ['SAML routed user to department', \Mockery::any()]);
    }

    public function test_system_owned_org_rule_catch_all_rescues_jit(): void
    {
        $system = System::factory()->create();
        $org = Organization::factory()->create();
        DB::table('SystemOrganizations')->insert([
            'SystemID' => $system->ID,
            'OrganizationID' => $org->ID,
        ]);

        $sysClient = SamlClient::factory()->forSystem($system->ID)->create([
            'slug' => 'sys',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
            'jit_enabled' => true,
            'department_id' => null,
        ]);

        SamlOrgRule::factory()->catchAll()->create([
            'saml_client_id' => $sysClient->id,
            'organization_id' => $org->ID,
        ]);

        $response = $this->post('/saml/sys/acs', [
            'SAMLResponse' => SamlResponseFactory::make([
                'destination' => url('/saml/sys/acs'),
                'attributes' => ['email' => 'rescued@acme.test', 'firstName' => 'R', 'lastName' => 'U'],
                'nameId' => 'rescued@acme.test',
            ]),
        ]);

        $response->assertRedirect('/finishAccountCreation');
        $response->assertSessionHas('Organization', $org->ID);
        $this->assertDatabaseHas('Users', ['Login' => 'rescued@acme.test', 'DepartmentID' => 0]);
    }

    public function test_system_owned_no_rules_still_rejects_unrouted(): void
    {
        $system = System::factory()->create();

        $sysClient = SamlClient::factory()->forSystem($system->ID)->create([
            'slug' => 'sys',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
            'jit_enabled' => true,
            'department_id' => null,
        ]);

        $response = $this->post('/saml/sys/acs', [
            'SAMLResponse' => SamlResponseFactory::make([
                'destination' => url('/saml/sys/acs'),
            ]),
        ]);

        $response->assertStatus(403);
        $this->assertGuest();
        $this->assertDatabaseMissing('Users', ['Login' => 'sso.user@acme.test']);
    }

    public function test_org_owned_zero_rules_is_byte_for_byte_unchanged(): void
    {
        // No routing rules at all on an org-owned client: placement resolves
        // organization_id from ownership, department_id null (no rules to
        // match) -> identical to the pre-routing default-department fallback.
        $response = $this->acs();

        $this->assertAuthenticated();
        $response->assertRedirect('/finishAccountCreation'); // DepartmentID 0
        $this->assertDatabaseHas('Users', ['Login' => 'sso.user@acme.test', 'DepartmentID' => 0]);
        $response->assertSessionHas('Organization', 933);
    }

    public function test_session_organization_reflects_routed_placement_for_org_owned_client(): void
    {
        $dept = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'ICU Nursing']);
        $this->departmentRule($this->client, 'ICU Nursing', 'icu');

        $response = $this->acs(['attributes' => [
            'email' => 'routed@acme.test', 'firstName' => 'R', 'lastName' => 'U', 'department' => 'Medical ICU',
        ], 'nameId' => 'routed@acme.test']);

        $response->assertSessionHas('Organization', $this->client->owner_id);
        $this->assertDatabaseHas('Users', ['Login' => 'routed@acme.test', 'DepartmentID' => $dept->ID]);
    }

    public function test_org_owned_default_department_survives_when_no_rule_matches(): void
    {
        $dept = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'Default Dept']);
        $this->client->update(['department_id' => $dept->ID]);
        // A department rule that will NOT match this login:
        SamlDepartmentRule::factory()->create(['saml_client_id' => $this->client->id,
            'attribute' => 'department', 'operator' => RoutingOperator::Equals, 'value' => 'nope', 'department_name' => 'Default Dept']);

        $this->acs(['attributes' => ['email' => 'defaulted@acme.test', 'firstName' => 'D', 'lastName' => 'F', 'department' => 'Radiology'], 'nameId' => 'defaulted@acme.test']);

        $this->assertDatabaseHas('Users', ['Login' => 'defaulted@acme.test', 'DepartmentID' => $dept->ID]);
    }

    public function test_resolving_department_rule_beats_the_static_default(): void
    {
        $default = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'Default Dept']);
        $routed = Department::factory()->create(['OrganizationID' => $this->client->owner_id, 'Name' => 'ICU Nursing']);
        $this->client->update(['department_id' => $default->ID]);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $this->client->id,
            'attribute' => 'department', 'operator' => RoutingOperator::Contains, 'value' => 'icu', 'department_name' => 'ICU Nursing']);

        $this->acs(['attributes' => ['email' => 'routed@acme.test', 'firstName' => 'R', 'lastName' => 'T', 'department' => 'Medical ICU'], 'nameId' => 'routed@acme.test']);

        $this->assertDatabaseHas('Users', ['Login' => 'routed@acme.test', 'DepartmentID' => $routed->ID]);
    }
}
