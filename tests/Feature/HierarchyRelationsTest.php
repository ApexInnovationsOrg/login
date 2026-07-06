<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HierarchyRelationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrg(int $id, string $name): void
    {
        DB::table('Countries')->updateOrInsert(['ID' => 231], ['Abbreviation' => 'US', 'Name' => 'United States']);
        DB::table('Organizations')->insert([
            'ID' => $id, 'Name' => $name, 'Address' => 'A', 'City' => 'C',
            'PostalCode' => '70001', 'Phone' => '555', 'CountryID' => 231,
            'CreationDate' => now()->format('Y-m-d'), 'PasswordMinLength' => 6,
            'PasswordComplexityNumeric' => 'N', 'PasswordComplexitySpecial' => 'N',
            'PasswordComplexityUppercase' => 'N', 'PasswordComplexityLowercase' => 'N',
        ]);
    }

    public function test_primary_key_is_id(): void
    {
        $this->makeOrg(7001, 'PK Org');

        $org = Organization::find(7001);

        $this->assertNotNull($org);
        $this->assertSame(7001, $org->id);        // ->id now resolves via $primaryKey = 'ID'
        $this->assertSame('ID', $org->getKeyName());
    }

    public function test_organization_has_many_departments(): void
    {
        $this->makeOrg(7001, 'PK Org');
        DB::table('Departments')->insert(['ID' => 8001, 'OrganizationID' => 7001, 'Name' => 'D1', 'Active' => 'Y']);
        DB::table('Departments')->insert(['ID' => 8002, 'OrganizationID' => 7001, 'Name' => 'D2', 'Active' => 'Y']);

        $this->assertCount(2, Organization::find(7001)->departments);
    }

    public function test_department_belongs_to_organization_and_has_users(): void
    {
        $this->makeOrg(7001, 'PK Org');
        DB::table('Departments')->insert(['ID' => 8001, 'OrganizationID' => 7001, 'Name' => 'D1', 'Active' => 'Y']);
        User::factory()->create(['DepartmentID' => 8001]);

        $dept = Department::find(8001);

        $this->assertSame(7001, $dept->organization->id);
        $this->assertCount(1, $dept->users);
    }

    public function test_existing_password_relation_still_resolves(): void
    {
        // The load-bearing chain User::department->org (used by getPasswordRequirements)
        // must keep working after the primary-key change.
        $this->makeOrg(7001, 'PK Org');
        DB::table('Departments')->insert(['ID' => 8001, 'OrganizationID' => 7001, 'Name' => 'D1', 'Active' => 'Y']);
        $user = User::factory()->create(['DepartmentID' => 8001]);

        $this->assertSame('PK Org', $user->department->org->Name);
    }
}
