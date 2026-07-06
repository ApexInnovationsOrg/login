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
}
