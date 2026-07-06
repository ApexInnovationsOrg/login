<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\System;
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
        $this->assertDatabaseCount('SystemOrganizations', 2);
    }

    public function test_organization_factory_satisfies_constraints_without_seeding(): void
    {
        $org = Organization::factory()->create();

        $this->assertDatabaseHas('Organizations', ['ID' => $org->ID]);
        $this->assertSame(231, (int) $org->CountryID);
    }

    public function test_organization_factory_honours_explicit_id_and_strict_state(): void
    {
        $org = Organization::factory()->strict()->create(['ID' => 933, 'Name' => 'SSO Organization']);

        $this->assertSame(933, $org->ID);
        $this->assertSame('SSO Organization', $org->Name);
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
}
