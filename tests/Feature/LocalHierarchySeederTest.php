<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\System;
use Database\Seeders\LocalHierarchySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalHierarchySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_a_system_tree_and_a_standalone_org_idempotently(): void
    {
        $this->seed(LocalHierarchySeeder::class);
        $this->seed(LocalHierarchySeeder::class); // idempotent

        $system = System::where('Name', 'Memorial Health System')->firstOrFail();
        $this->assertSame(1, System::where('Name', 'Memorial Health System')->count());
        $this->assertCount(3, $system->organizations);
        foreach ($system->organizations as $org) {
            $this->assertCount(4, $org->departments);
        }

        $standalone = Organization::where('Name', 'Standalone Community Hospital')->firstOrFail();
        $this->assertNull($standalone->system);
        $this->assertSame(3, Department::where('OrganizationID', $standalone->ID)->count());
    }
}
