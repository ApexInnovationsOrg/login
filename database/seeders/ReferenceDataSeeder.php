<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Organization;
use App\Models\System;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Seed the lookup tables the login flows depend on.
     *
     * IDs are explicit because several are hardcoded in the application:
     * StateID 66 and Organization 933 (SAML listener in EventServiceProvider),
     * ProfessionalRole 3 (EMS branch in FinishUserCreation).
     *
     * @return void
     */
    public function run()
    {
        DB::table('Countries')->insert([
            ['ID' => 1, 'Abbreviation' => 'CA', 'Name' => 'Canada'],
            ['ID' => 231, 'Abbreviation' => 'US', 'Name' => 'United States'], // Organizations.CountryID default, FK to Countries
        ]);

        DB::table('States')->insert([
            ['ID' => 1, 'Abbreviation' => 'LA', 'Name' => 'Louisiana'],
            ['ID' => 2, 'Abbreviation' => 'CA', 'Name' => 'California'],
            ['ID' => 66, 'Abbreviation' => 'TX', 'Name' => 'Texas'],
        ]);

        Organization::factory()->create(['ID' => 1, 'Name' => 'Local Dev Organization']);
        Organization::factory()->strict()->create(['ID' => 2, 'Name' => 'Strict Password Organization']);
        Organization::factory()->create(['ID' => 933, 'Name' => 'SSO Organization']);

        Department::factory()->create(['ID' => 1, 'OrganizationID' => 1, 'Name' => 'General']);
        Department::factory()->create(['ID' => 2, 'OrganizationID' => 2, 'Name' => 'Strict Department']);
        Department::factory()->create(['ID' => 3, 'OrganizationID' => 933, 'Name' => 'SSO Department']);

        // Local system spanning orgs 1 and 2 so system-owned clients are
        // testable locally; org 933 stays system-less (degenerate case).
        System::factory()->create(['ID' => 1, 'Name' => 'Local Health System']);
        DB::table('SystemOrganizations')->insert([
            ['SystemID' => 1, 'OrganizationID' => 1],
            ['SystemID' => 1, 'OrganizationID' => 2],
        ]);

        DB::table('Credentials')->insert([
            ['ID' => 1, 'Name' => 'RN'],
            ['ID' => 2, 'Name' => 'MD'],
            ['ID' => 3, 'Name' => 'EMT'],
        ]);

        DB::table('ProfessionalRoles')->insert([
            ['ID' => 1, 'Name' => 'Nurse'],
            ['ID' => 2, 'Name' => 'Physician'],
            ['ID' => 3, 'Name' => 'EMS Professional'],
        ]);

        DB::table('ProfessionalCredentialFilters')->insert([
            ['ID' => 1, 'ProfessionalRoleID' => 1, 'CredentialID' => 1],
            ['ID' => 2, 'ProfessionalRoleID' => 2, 'CredentialID' => 2],
            ['ID' => 3, 'ProfessionalRoleID' => 3, 'CredentialID' => 3],
        ]);

        DB::table('CredentialLicenseTypes')->insert([
            ['ID' => 1, 'Name' => 'State License'],
            ['ID' => 2, 'Name' => 'National Registry'],
        ]);
    }
}
