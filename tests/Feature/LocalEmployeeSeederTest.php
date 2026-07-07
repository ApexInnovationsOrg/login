<?php

namespace Tests\Feature;

use Database\Seeders\LocalEmployeeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocalEmployeeSeederTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_seeds_an_active_portal_admin_with_legacy_hash(): void
    {
        $this->seed(LocalEmployeeSeeder::class);
        $this->seed(LocalEmployeeSeeder::class); // idempotent

        $rows = DB::table('Employees')->where('Email', 'dev.admin@apexinnovations.com')->get();

        $this->assertCount(1, $rows);
        $this->assertSame('Y', $rows[0]->Active);
        $this->assertSame(md5('p6^8&password'), $rows[0]->Password);
        $this->assertTrue(strtotime($rows[0]->PasswordLastChanged) > strtotime('-1 day'));
    }
}
