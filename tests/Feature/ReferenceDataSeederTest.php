<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReferenceDataSeederTest extends TestCase
{
    use RefreshDatabase;

    // RefreshDatabase only runs "migrate:fresh --seed" once for the whole suite
    // (see RefreshDatabaseState::$migrated); calling $this->seed() again here would
    // re-insert the same fixed-ID rows and collide. $seed = true reuses that single
    // seed pass, same as the other $seed = true tests (PasswordResetTest, etc.).
    protected $seed = true;

    public function test_seeder_creates_fixed_organizations_and_departments(): void
    {
        $this->assertDatabaseHas('Organizations', ['ID' => 1, 'Name' => 'Local Dev Organization']);
        $this->assertDatabaseHas('Organizations', ['ID' => 933, 'Name' => 'SSO Organization']);
        $this->assertDatabaseHas('Departments', ['ID' => 1, 'OrganizationID' => 1]);
        $this->assertDatabaseHas('Departments', ['ID' => 3, 'OrganizationID' => 933]);

        // Org 2 keeps the strict-password preset.
        $org2 = DB::table('Organizations')->where('ID', 2)->first();
        $this->assertSame(12, (int) $org2->PasswordMinLength);
        $this->assertSame('Y', $org2->PasswordComplexityNumeric);
    }

    public function test_seeding_commits_no_random_organizations(): void
    {
        // The seed pass is committed outside test transactions, so any
        // faker-named organization it leaves behind can later collide with a
        // test-created org (Organizations.Name is unique and faker's unique()
        // memory resets each test). Only the fixed rows may exist.
        $this->assertSame(
            ['Local Dev Organization', 'SSO Organization', 'Strict Password Organization'],
            DB::table('Organizations')->orderBy('Name')->pluck('Name')->all()
        );

        // The dev user rides on seeded Department 1 instead of minting
        // a department/organization pair of its own. (Matched by name, not
        // Login, so the assertion survives dev-domain renames.)
        $this->assertDatabaseHas('Users', ['FirstName' => 'Dev', 'LastName' => 'User', 'DepartmentID' => 1]);
    }
}
