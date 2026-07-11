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
use Illuminate\Support\Facades\Log;
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

    public function test_duplicate_department_names_resolve_to_the_lowest_id(): void
    {
        // The legacy Departments table enforces UNIQUE(Name, OrganizationID)
        // for all current writes, so two active rows with the same name in
        // one org can't be constructed through normal inserts (verified:
        // even case- and trailing-space variants collide under this
        // column's case-insensitive, pad-space collation). The resolver
        // still has to behave deterministically if it ever sees legacy data
        // that predates the constraint, so pin the query shape directly:
        // the department lookup orders by ID descending before the
        // name-keyed pluck, so a later (lower-ID) row always overwrites an
        // earlier (higher-ID) one for the same name — the lowest ID wins.
        $org = Organization::factory()->create();
        Department::factory()->create(['OrganizationID' => $org->ID, 'Name' => 'Radiology']);
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);
        SamlDepartmentRule::factory()->catchAll()->create(['saml_client_id' => $client->id, 'department_name' => 'Radiology']);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        app(AttributeRouter::class)->route($client, []);

        $departmentQuery = collect($queries)->first(fn ($sql) => str_contains($sql, 'from `Departments`'));
        $this->assertNotNull($departmentQuery, 'Expected a Departments query to run.');
        $this->assertStringContainsString('order by `ID` desc', $departmentQuery);
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

    public function test_stale_out_of_scope_org_rule_is_skipped_with_warning(): void
    {
        [$system, $orgA, $orgB] = $this->seedSystemWithTwoOrgs();
        $outsideOrg = Organization::factory()->create(); // never added to the system's pivot rows
        $client = SamlClient::factory()->forSystem($system->ID)->create(['department_id' => null]);

        // Earlier, matching rule targets an org no longer in scope (stale
        // re-parent); a later rule targets an in-scope org and should win.
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'position' => 1,
            'attribute' => '*', 'operator' => RoutingOperator::Wildcard, 'value' => '*', 'organization_id' => $outsideOrg->ID]);
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'position' => 2,
            'attribute' => '*', 'operator' => RoutingOperator::Wildcard, 'value' => '*', 'organization_id' => $orgA->ID]);

        Log::spy();

        $placement = app(AttributeRouter::class)->route($client, []);

        $this->assertSame($orgA->ID, $placement['organization_id']);
        Log::shouldHaveReceived('warning')->withArgs(function (string $message, array $context) use ($client, $outsideOrg) {
            return $message === 'Routing rule targets organization outside client scope'
                && ($context['client'] ?? null) === $client->slug
                && ($context['organization_id'] ?? null) === $outsideOrg->ID;
        })->once();
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
