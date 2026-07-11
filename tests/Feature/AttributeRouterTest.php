<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Models\System;
use App\Saml\AttributeRouter;
use App\Saml\RoutingOperator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttributeRouterTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_owned_ignores_org_rules_and_routes_department_by_name(): void
    {
        $org = Organization::factory()->create();
        $dept = Department::factory()->create(['OrganizationID' => $org->ID, 'Name' => 'ICU Nursing']);
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'organization_id' => 999999]); // ignored
        SamlDepartmentRule::factory()->create([
            'saml_client_id' => $client->id, 'attribute' => 'department',
            'operator' => RoutingOperator::Contains, 'value' => 'icu', 'department_name' => 'icu nursing',
        ]);

        $placement = app(AttributeRouter::class)->route($client, ['department' => ['Medical ICU']]);

        $this->assertSame(['organization_id' => $org->ID, 'department_id' => $dept->ID], $placement);
    }

    public function test_system_owned_stage_one_first_match_wins(): void
    {
        [$system, $orgA, $orgB] = $this->seedSystemWithTwoOrgs(); // helper: System + 2 orgs + pivot rows
        $client = SamlClient::factory()->forSystem($system->ID)->create(['department_id' => null]);
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'position' => 1,
            'attribute' => 'hospital', 'operator' => RoutingOperator::Equals, 'value' => 'mercy west', 'organization_id' => $orgA->ID]);
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'position' => 2,
            'attribute' => 'hospital', 'operator' => RoutingOperator::Wildcard, 'value' => '*', 'organization_id' => $orgB->ID]);

        $this->assertSame($orgA->ID, app(AttributeRouter::class)->route($client, ['hospital' => ['Mercy West']])['organization_id']);
        $this->assertSame($orgB->ID, app(AttributeRouter::class)->route($client, ['hospital' => ['Other']])['organization_id']);
    }

    public function test_system_owned_no_match_uses_fallback_then_null(): void
    {
        [$system] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create(['department_id' => null]);

        $this->assertNull(app(AttributeRouter::class)->route($client, []));
        $this->assertSame(42, app(AttributeRouter::class)->route($client, [], 42)['organization_id']);
    }

    public function test_department_fall_through_when_name_missing_in_resolved_org(): void
    {
        $org = Organization::factory()->create();
        Department::factory()->create(['OrganizationID' => $org->ID, 'Name' => 'General']);
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $client->id, 'position' => 1,
            'attribute' => 'group', 'operator' => RoutingOperator::Contains, 'value' => 'nurse', 'department_name' => 'Cath Lab']);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $client->id, 'position' => 2,
            'attribute' => 'group', 'operator' => RoutingOperator::Contains, 'value' => 'nurse', 'department_name' => 'General']);

        $placement = app(AttributeRouter::class)->route($client, ['group' => ['ICU-Nurses']]);

        $this->assertSame('General', Department::find($placement['department_id'])->Name);
    }

    public function test_inactive_departments_never_resolve(): void
    {
        $org = Organization::factory()->create();
        Department::factory()->create(['OrganizationID' => $org->ID, 'Name' => 'Old Wing', 'Active' => 'N']);
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);
        SamlDepartmentRule::factory()->catchAll()->create(['saml_client_id' => $client->id, 'department_name' => 'Old Wing']);

        $this->assertNull(app(AttributeRouter::class)->route($client, [])['department_id']);
    }

    public function test_catch_all_org_rule_matches_empty_assertion(): void
    {
        [$system, $orgA] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create(['department_id' => null]);
        SamlOrgRule::factory()->catchAll()->create(['saml_client_id' => $client->id, 'organization_id' => $orgA->ID]);

        $this->assertSame($orgA->ID, app(AttributeRouter::class)->route($client, [])['organization_id']);
    }

    /** @return array{0: System, 1: Organization, 2: Organization} */
    private function seedSystemWithTwoOrgs(): array
    {
        $system = System::factory()->create();
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $orgA->ID]);
        DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $orgB->ID]);

        return [$system, $orgA, $orgB];
    }
}
