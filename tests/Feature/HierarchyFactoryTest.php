<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\System;
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
}
