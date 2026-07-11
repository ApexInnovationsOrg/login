<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\Support\InteractsWithAdminApi;
use Tests\TestCase;

class AdminRoutingRulesTest extends TestCase
{
    use InteractsWithAdminApi;
    use RefreshDatabase;

    private function headers(): array
    {
        return $this->adminApiHeaders();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureAdminApi();
    }

    public function test_get_returns_ordered_rules_with_catch_all_flags(): void
    {
        $system = System::factory()->create();
        $orgA = Organization::factory()->create(['Name' => 'Mercy West']);
        DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $orgA->ID]);
        $client = SamlClient::factory()->forSystem($system->ID)->create(['slug' => 'acme']);

        SamlOrgRule::factory()->create([
            'saml_client_id' => $client->id,
            'position' => 1,
            'attribute' => 'hospital',
            'operator' => 'equals',
            'value' => 'Mercy West',
            'organization_id' => $orgA->ID,
        ]);
        SamlOrgRule::factory()->catchAll()->create([
            'saml_client_id' => $client->id,
            'position' => 2,
            'organization_id' => $orgA->ID,
        ]);

        SamlDepartmentRule::factory()->create([
            'saml_client_id' => $client->id,
            'position' => 1,
            'attribute' => 'group',
            'operator' => 'contains',
            'value' => 'nurse',
            'department_name' => 'ICU Nursing',
        ]);
        SamlDepartmentRule::factory()->catchAll()->create([
            'saml_client_id' => $client->id,
            'position' => 2,
            'department_name' => 'General',
        ]);

        $response = $this->getJson('/api/admin/saml-clients/acme/routing-rules', $this->headers())->assertOk();

        $response->assertJsonPath('data.org_rules.0.attribute', 'hospital');
        $response->assertJsonPath('data.org_rules.0.operator', 'equals');
        $response->assertJsonPath('data.org_rules.0.value', 'Mercy West');
        $response->assertJsonPath('data.org_rules.0.organization_id', $orgA->ID);
        $response->assertJsonPath('data.org_rules.0.organization_name', 'Mercy West');
        $response->assertJsonPath('data.org_rules.0.catch_all', false);
        $response->assertJsonPath('data.org_rules.1.attribute', '*');
        $response->assertJsonPath('data.org_rules.1.catch_all', true);

        $response->assertJsonPath('data.department_rules.0.attribute', 'group');
        $response->assertJsonPath('data.department_rules.0.operator', 'contains');
        $response->assertJsonPath('data.department_rules.0.value', 'nurse');
        $response->assertJsonPath('data.department_rules.0.department_name', 'ICU Nursing');
        $response->assertJsonPath('data.department_rules.0.catch_all', false);
        $response->assertJsonPath('data.department_rules.1.catch_all', true);
    }

    public function test_put_replaces_rules_and_returns_get_shape(): void
    {
        $system = System::factory()->create();
        $orgA = Organization::factory()->create(['Name' => 'Mercy West']);
        DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $orgA->ID]);
        $client = SamlClient::factory()->forSystem($system->ID)->create(['slug' => 'acme']);

        $payload = [
            'org_rules' => [
                ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'Mercy West', 'organization_id' => $orgA->ID],
            ],
            'department_rules' => [
                ['attribute' => 'group', 'operator' => 'contains', 'value' => 'nurse', 'department_name' => 'ICU Nursing'],
            ],
        ];

        $response = $this->putJson('/api/admin/saml-clients/acme/routing-rules', $payload, $this->headers())
            ->assertOk();

        $response->assertJsonPath('data.org_rules.0.attribute', 'hospital');
        $response->assertJsonPath('data.org_rules.0.operator', 'equals');
        $response->assertJsonPath('data.org_rules.0.organization_name', 'Mercy West');
        $response->assertJsonPath('data.department_rules.0.department_name', 'ICU Nursing');

        $this->assertDatabaseCount('saml_org_rules', 1);
        $this->assertDatabaseCount('saml_department_rules', 1);
    }

    public function test_put_validation_failure_surfaces_manager_keys(): void
    {
        $org = Organization::factory()->create();
        $client = SamlClient::factory()->create(['slug' => 'acme', 'owner_id' => $org->ID]);

        $payload = [
            'org_rules' => [
                ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'x', 'organization_id' => $org->ID],
            ],
            'department_rules' => [],
        ];

        $response = $this->putJson('/api/admin/saml-clients/acme/routing-rules', $payload, $this->headers())
            ->assertStatus(422);

        $response->assertJsonValidationErrors(['org_rules']);
    }

    public function test_put_logs_audit_with_slug_counts_and_tuples(): void
    {
        Log::spy();

        $system = System::factory()->create();
        $orgA = Organization::factory()->create();
        DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $orgA->ID]);
        $client = SamlClient::factory()->forSystem($system->ID)->create(['slug' => 'acme']);

        $payload = [
            'org_rules' => [
                ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'Mercy West', 'organization_id' => $orgA->ID],
            ],
            'department_rules' => [
                ['attribute' => 'group', 'operator' => 'contains', 'value' => 'nurse', 'department_name' => 'ICU Nursing'],
            ],
        ];

        $this->putJson('/api/admin/saml-clients/acme/routing-rules', $payload, $this->headers())->assertOk();

        Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context) {
            return $message === 'admin api: replace routing rules'
                && ($context['slug'] ?? null) === 'acme'
                && ($context['org_rule_count'] ?? null) === 1
                && ($context['department_rule_count'] ?? null) === 1
                && isset($context['org_rules'][0]['attribute'])
                && isset($context['department_rules'][0]['attribute']);
        })->once();
    }

    public function test_routable_organizations_for_org_owned_client(): void
    {
        $org = Organization::factory()->create(['Name' => 'Solo Org']);
        SamlClient::factory()->create(['slug' => 'acme', 'owner_id' => $org->ID]);
        Organization::factory()->create(['Name' => 'Unrelated Org']);

        $response = $this->getJson('/api/admin/saml-clients/acme/routable-organizations', $this->headers())
            ->assertOk();

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $org->ID);
        $response->assertJsonPath('data.0.name', 'Solo Org');
    }

    public function test_routable_organizations_for_system_owned_client_ordered_by_name(): void
    {
        $system = System::factory()->create();
        $orgB = Organization::factory()->create(['Name' => 'Zeta Health']);
        $orgA = Organization::factory()->create(['Name' => 'Alpha Health']);
        DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $orgB->ID]);
        DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $orgA->ID]);
        SamlClient::factory()->forSystem($system->ID)->create(['slug' => 'acme']);

        $response = $this->getJson('/api/admin/saml-clients/acme/routable-organizations', $this->headers())
            ->assertOk();

        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.name', 'Alpha Health');
        $response->assertJsonPath('data.1.name', 'Zeta Health');
    }

    public function test_routing_rule_routes_require_a_valid_token(): void
    {
        $client = SamlClient::factory()->create(['slug' => 'acme']);

        $getRoutes = [
            "/api/admin/saml-clients/{$client->slug}/routing-rules",
            "/api/admin/saml-clients/{$client->slug}/routable-organizations",
        ];

        foreach ($getRoutes as $route) {
            $this->getJson($route)->assertUnauthorized();
            $this->getJson($route, ['Authorization' => 'Bearer wrong-token'])->assertUnauthorized();
        }

        $putPayload = ['org_rules' => [], 'department_rules' => []];
        $this->putJson("/api/admin/saml-clients/{$client->slug}/routing-rules", $putPayload)->assertUnauthorized();
        $this->putJson("/api/admin/saml-clients/{$client->slug}/routing-rules", $putPayload, ['Authorization' => 'Bearer wrong-token'])->assertUnauthorized();
    }
}
