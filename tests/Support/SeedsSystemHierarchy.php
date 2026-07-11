<?php

namespace Tests\Support;

use App\Models\Organization;
use App\Models\System;
use Illuminate\Support\Facades\DB;

trait SeedsSystemHierarchy
{
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
