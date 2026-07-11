<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\System;
use App\Models\SystemOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchyFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_factory_creates_a_system(): void
    {
        $system = System::factory()->create();

        $this->assertNotNull($system->ID);
        $this->assertDatabaseHas('Systems', ['ID' => $system->ID]);
    }

    public function test_system_has_organizations_through_join(): void
    {
        $system = System::factory()
            ->has(Organization::factory()->count(2), 'organizations')
            ->create();

        $this->assertCount(2, $system->organizations);
        $this->assertDatabaseHas('SystemOrganizations', ['SystemID' => $system->ID]);
    }

    public function test_organization_system_relation_returns_owning_system(): void
    {
        $system = System::factory()->create();
        $org = Organization::factory()->create();
        SystemOrganization::create(['SystemID' => $system->ID, 'OrganizationID' => $org->ID]);

        $this->assertNotNull($org->system);
        $this->assertSame($system->ID, $org->system->ID);
    }

    public function test_organization_without_system_returns_null(): void
    {
        $org = Organization::factory()->create();

        $this->assertNull($org->system);
    }

    public function test_organization_factory_satisfies_constraints_without_seeding(): void
    {
        $org = Organization::factory()->create();

        $this->assertDatabaseHas('Organizations', ['ID' => $org->ID]);
        $this->assertSame(231, (int) $org->CountryID);
    }

    public function test_organization_factory_honours_explicit_id_and_strict_state(): void
    {
        $org = Organization::factory()->strict()->create(['ID' => 909933, 'Name' => 'Explicit ID Organization']);

        $this->assertSame(909933, $org->ID);
        $this->assertSame('Explicit ID Organization', $org->Name);
        $this->assertSame(12, (int) $org->PasswordMinLength);
        $this->assertSame('Y', $org->PasswordComplexityNumeric);
    }

    public function test_department_factory_brings_its_own_organization(): void
    {
        $dept = Department::factory()->create();

        $this->assertNotNull($dept->OrganizationID);
        $this->assertSame('Y', $dept->Active);
        $this->assertNotNull($dept->organization);
    }

    public function test_department_factory_for_organization(): void
    {
        $org = Organization::factory()->create();
        $dept = Department::factory()->for($org)->create();

        $this->assertSame($org->ID, $dept->organization->ID);
    }

    public function test_user_factory_builds_department_and_organization_chain(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->department);
        $this->assertNotNull($user->department->organization);
    }

    public function test_make_admin_helpers_insert_one_idempotent_row(): void
    {
        $system = System::factory()->create();
        $org = Organization::factory()->create();
        $dept = Department::factory()->for($org)->create();
        $user = User::factory()->create(['DepartmentID' => $dept->ID]);

        $user->makeDepartmentAdmin($dept);
        $user->makeDepartmentAdmin($dept); // idempotent
        $user->makeOrganizationAdmin($org);
        $user->makeSystemAdmin($system);

        $this->assertDatabaseCount('DepartmentAdmins', 1);
        $this->assertDatabaseCount('OrganizationAdmins', 1);
        $this->assertDatabaseCount('SystemAdmins', 1);

        $this->assertTrue($user->adminDepartments->contains('ID', $dept->ID));
        $this->assertTrue($user->adminOrganizations->contains('ID', $org->ID));
        $this->assertTrue($user->adminSystems->contains('ID', $system->ID));
    }

    public function test_for_system_with_string_shares_one_system_across_orgs(): void
    {
        $orgA = Organization::factory()->forSystem('Memorial Health System')->create();
        $orgB = Organization::factory()->forSystem('Memorial Health System')->create();

        $this->assertSame(1, System::where('Name', 'Memorial Health System')->count());
        $this->assertSame($orgA->system->ID, $orgB->system->ID);
        $this->assertDatabaseCount('SystemOrganizations', 2);
    }

    public function test_for_system_with_model_attaches_to_it(): void
    {
        $system = System::factory()->create();
        $org = Organization::factory()->forSystem($system)->create();

        $this->assertSame($system->ID, $org->system->ID);
    }

    public function test_for_system_without_argument_creates_a_fresh_system(): void
    {
        $org = Organization::factory()->forSystem()->create();

        $this->assertNotNull($org->system);
    }

    public function test_reattaching_an_org_replaces_its_system(): void
    {
        $first = System::factory()->create();
        $second = System::factory()->create();
        $org = Organization::factory()->forSystem($first)->create();

        SystemOrganization::updateOrCreate(
            ['OrganizationID' => $org->ID],
            ['SystemID' => $second->ID],
        );

        $this->assertSame(1, SystemOrganization::where('OrganizationID', $org->ID)->count());
        $this->assertSame($second->ID, $org->fresh()->system->ID);
    }

    public function test_with_departments_count_creates_distinct_names(): void
    {
        $org = Organization::factory()->withDepartments(4)->create();

        $names = $org->departments->pluck('Name');
        $this->assertCount(4, $names);
        $this->assertCount(4, $names->unique());
    }

    public function test_with_departments_accepts_explicit_names(): void
    {
        $org = Organization::factory()->withDepartments(['Emergency', 'ICU'])->create();

        $this->assertEqualsCanonicalizing(
            ['Emergency', 'ICU'],
            $org->departments->pluck('Name')->all(),
        );
    }
}
