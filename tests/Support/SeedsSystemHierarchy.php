<?php

namespace Tests\Support;

use App\Models\Organization;
use App\Models\System;

trait SeedsSystemHierarchy
{
    /** @return array{0: System, 1: Organization, 2: Organization} */
    private function seedSystemWithTwoOrgs(): array
    {
        $system = System::factory()->create();
        $orgA = Organization::factory()->forSystem($system)->create();
        $orgB = Organization::factory()->forSystem($system)->create();

        return [$system, $orgA, $orgB];
    }
}
