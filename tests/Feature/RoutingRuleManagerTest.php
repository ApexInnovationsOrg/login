<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Models\System;
use App\Saml\SamlClientManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoutingRuleManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_replace_persists_both_lists_in_order_and_removes_old_rules(): void
    {
        [$system, $orgA, $orgB] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create();
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'organization_id' => $orgA->ID]);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $client->id]);

        $orgRules = [
            ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'mercy west', 'organization_id' => $orgA->ID],
            ['attribute' => '*', 'operator' => 'wildcard', 'value' => '*', 'organization_id' => $orgB->ID],
        ];
        $departmentRules = [
            ['attribute' => 'group', 'operator' => 'contains', 'value' => 'nurse', 'department_name' => 'ICU'],
            ['attribute' => '*', 'operator' => 'wildcard', 'value' => '*', 'department_name' => 'General'],
        ];

        $result = app(SamlClientManager::class)->replaceRoutingRules($client, $orgRules, $departmentRules);

        $this->assertInstanceOf(SamlClient::class, $result);
        $this->assertDatabaseCount('saml_org_rules', 2);
        $this->assertDatabaseCount('saml_department_rules', 2);

        $storedOrgRules = SamlOrgRule::where('saml_client_id', $client->id)->orderBy('position')->get();
        $this->assertSame(1, $storedOrgRules[0]->position);
        $this->assertSame('hospital', $storedOrgRules[0]->attribute);
        $this->assertSame(2, $storedOrgRules[1]->position);
        $this->assertSame('*', $storedOrgRules[1]->attribute);

        $storedDeptRules = SamlDepartmentRule::where('saml_client_id', $client->id)->orderBy('position')->get();
        $this->assertSame(1, $storedDeptRules[0]->position);
        $this->assertSame('ICU', $storedDeptRules[0]->department_name);
        $this->assertSame(2, $storedDeptRules[1]->position);
        $this->assertSame('General', $storedDeptRules[1]->department_name);
    }

    public function test_org_rules_on_org_owned_client_rejected(): void
    {
        $org = Organization::factory()->create();
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);

        $orgRules = [
            ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'x', 'organization_id' => $org->ID],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, $orgRules, []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('org_rules', $e->errors());
            $this->assertSame('Organization rules require a system-owned client.', $e->errors()['org_rules'][0]);
        }
    }

    public function test_out_of_scope_organization_rejected(): void
    {
        [$system] = $this->seedSystemWithTwoOrgs();
        $outsideOrg = Organization::factory()->create();
        $client = SamlClient::factory()->forSystem($system->ID)->create();

        $orgRules = [
            ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'x', 'organization_id' => $outsideOrg->ID],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, $orgRules, []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('org_rules.0.organization_id', $e->errors());
            $this->assertSame(
                "Organization is outside this client's scope.",
                $e->errors()['org_rules.0.organization_id'][0]
            );
        }
    }

    public function test_unknown_operator_string_rejected(): void
    {
        [$system, $orgA] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create();

        $orgRules = [
            ['attribute' => 'hospital', 'operator' => 'regex', 'value' => 'x', 'organization_id' => $orgA->ID],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, $orgRules, []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('org_rules.0.operator', $e->errors());
        }
    }

    public function test_wildcard_attribute_with_non_triple_rejected(): void
    {
        [$system, $orgA] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create();

        $orgRules = [
            ['attribute' => '*', 'operator' => 'equals', 'value' => '*', 'organization_id' => $orgA->ID],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, $orgRules, []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('org_rules.0.attribute', $e->errors());
        }
    }

    public function test_rule_after_catch_all_rejected(): void
    {
        [$system, $orgA] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create();

        $orgRules = [
            ['attribute' => '*', 'operator' => 'wildcard', 'value' => '*', 'organization_id' => $orgA->ID],
            ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'x', 'organization_id' => $orgA->ID],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, $orgRules, []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('org_rules.1', $e->errors());
            $this->assertSame('Rules after a catch-all are unreachable.', $e->errors()['org_rules.1'][0]);
        }
    }

    public function test_department_rule_after_catch_all_rejected(): void
    {
        [$system, $orgA] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create();

        $departmentRules = [
            ['attribute' => '*', 'operator' => 'wildcard', 'value' => '*', 'department_name' => 'General'],
            ['attribute' => 'group', 'operator' => 'contains', 'value' => 'nurse', 'department_name' => 'ICU'],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, [], $departmentRules);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('department_rules.1', $e->errors());
            $this->assertSame('Rules after a catch-all are unreachable.', $e->errors()['department_rules.1'][0]);
        }
    }

    public function test_department_rule_missing_name_rejected(): void
    {
        $org = Organization::factory()->create();
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);

        $departmentRules = [
            ['attribute' => 'group', 'operator' => 'contains', 'value' => 'nurse', 'department_name' => ''],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, [], $departmentRules);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('department_rules.0.department_name', $e->errors());
        }
    }

    public function test_empty_lists_replace_clears_everything(): void
    {
        [$system, $orgA] = $this->seedSystemWithTwoOrgs();
        $client = SamlClient::factory()->forSystem($system->ID)->create();
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'organization_id' => $orgA->ID]);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $client->id]);

        app(SamlClientManager::class)->replaceRoutingRules($client, [], []);

        $this->assertDatabaseCount('saml_org_rules', 0);
        $this->assertDatabaseCount('saml_department_rules', 0);
    }

    public function test_failed_validation_leaves_prior_rules_intact(): void
    {
        [$system, $orgA] = $this->seedSystemWithTwoOrgs();
        $outsideOrg = Organization::factory()->create();
        $client = SamlClient::factory()->forSystem($system->ID)->create();
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'organization_id' => $orgA->ID]);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $client->id]);

        $this->assertDatabaseCount('saml_org_rules', 1);
        $this->assertDatabaseCount('saml_department_rules', 1);

        $badOrgRules = [
            ['attribute' => 'hospital', 'operator' => 'equals', 'value' => 'x', 'organization_id' => $outsideOrg->ID],
        ];

        try {
            app(SamlClientManager::class)->replaceRoutingRules($client, $badOrgRules, []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            // expected
        }

        $this->assertDatabaseCount('saml_org_rules', 1);
        $this->assertDatabaseCount('saml_department_rules', 1);
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
