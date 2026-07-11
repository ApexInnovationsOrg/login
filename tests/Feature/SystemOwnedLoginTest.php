<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\System;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Support\SamlResponseFactory;
use Tests\TestCase;

class SystemOwnedLoginTest extends TestCase
{
    use RefreshDatabase;

    // First-alphabetical RefreshDatabase caution does not apply (SystemOwnedLoginTest
    // sorts after AdminApiSsoHandoffTest, which owns the seed pass).

    private SamlClient $client;

    private System $system;

    private Organization $orgInSystem;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
            'saml.replay_store' => 'array',
        ]);

        $this->system = System::factory()->create();
        $this->orgInSystem = Organization::factory()->create();
        DB::table('SystemOrganizations')->insert([
            'SystemID' => $this->system->ID,
            'OrganizationID' => $this->orgInSystem->ID,
        ]);

        $this->client = SamlClient::factory()->forSystem($this->system->ID)->create([
            'slug' => 'sys',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
            'jit_enabled' => true,
            'department_id' => null,
        ]);
    }

    private function acs(array $responseOverrides = []): TestResponse
    {
        return $this->post('/saml/sys/acs', [
            'SAMLResponse' => SamlResponseFactory::make(['destination' => url('/saml/sys/acs')] + $responseOverrides),
        ]);
    }

    public function test_new_jit_user_on_system_client_is_rejected(): void
    {
        $response = $this->acs(); // sso.user@acme.test does not exist

        $response->assertStatus(403);
        $this->assertGuest();
        $this->assertDatabaseMissing('Users', ['Login' => 'sso.user@acme.test']);
    }

    public function test_existing_user_gets_department_org_in_session(): void
    {
        $dept = Department::factory()->create(['OrganizationID' => $this->orgInSystem->ID]);
        User::factory()->create(['Login' => 'done@acme.test', 'DepartmentID' => $dept->ID]);

        $response = $this->acs(['nameId' => 'done@acme.test', 'attributes' => [
            'email' => 'done@acme.test', 'firstName' => 'Done', 'lastName' => 'User',
        ]]);

        $response->assertRedirect('https://www.apexinnovations.com/MyCurriculum.php');
        $response->assertSessionHas('Organization', $this->orgInSystem->ID);
    }

    public function test_org_owned_jit_fallback_is_unchanged(): void
    {
        // Sibling org-owned client on the same fixtures: JIT proceeds to finish flow.
        $orgClient = SamlClient::factory()->create([
            'slug' => 'acme2', 'jit_enabled' => true, 'department_id' => null,
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
        ]);

        $this->post('/saml/acme2/acs', ['SAMLResponse' => SamlResponseFactory::make([
            'destination' => url('/saml/acme2/acs'),
        ])])->assertRedirect('/finishAccountCreation');

        $this->assertDatabaseHas('Users', ['Login' => 'sso.user@acme.test', 'DepartmentID' => 0]);
    }
}
