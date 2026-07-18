<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\System;
use Illuminate\Database\Seeder;

/**
 * Local-dev hierarchy for browser testing: one system with three orgs of
 * four departments each, plus a standalone org (no system) with three —
 * the no-system shape exists in production data and must stay exercised.
 * Guarded by name so re-running never duplicates.
 *
 *   php artisan db:seed --class=LocalHierarchySeeder
 */
class LocalHierarchySeeder extends Seeder
{
    public function run(): void
    {
        if (! System::where('Name', 'Memorial Health System')->exists()) {
            System::factory()
                ->withOrganizations(3, departmentsEach: 4)
                ->create(['Name' => 'Memorial Health System']);
        }

        if (! Organization::where('Name', 'Standalone Community Hospital')->exists()) {
            Organization::factory()
                ->withDepartments(3)
                ->create(['Name' => 'Standalone Community Hospital']);
        }
    }
}
