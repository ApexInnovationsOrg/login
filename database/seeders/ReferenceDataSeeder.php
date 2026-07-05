<?php

namespace Database\Seeders;

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
        DB::table('States')->insert([
            ['ID' => 1, 'Abbreviation' => 'LA', 'Name' => 'Louisiana'],
            ['ID' => 2, 'Abbreviation' => 'CA', 'Name' => 'California'],
            ['ID' => 66, 'Abbreviation' => 'TX', 'Name' => 'Texas'],
        ]);

        DB::table('Organizations')->insert([
            [
                'ID' => 1,
                'Name' => 'Local Dev Organization',
                'CreationDate' => now()->format('Y-m-d H:i:s'),
                'PasswordMinLength' => 6,
                'PasswordComplexityNumeric' => 'N',
                'PasswordComplexitySpecial' => 'N',
                'PasswordComplexityUppercase' => 'N',
                'PasswordComplexityLowercase' => 'N',
            ],
            [
                'ID' => 2,
                'Name' => 'Strict Password Organization',
                'CreationDate' => now()->format('Y-m-d H:i:s'),
                'PasswordMinLength' => 12,
                'PasswordComplexityNumeric' => 'Y',
                'PasswordComplexitySpecial' => 'Y',
                'PasswordComplexityUppercase' => 'Y',
                'PasswordComplexityLowercase' => 'Y',
            ],
            [
                'ID' => 933,
                'Name' => 'SSO Organization',
                'CreationDate' => now()->format('Y-m-d H:i:s'),
                'PasswordMinLength' => 6,
                'PasswordComplexityNumeric' => 'N',
                'PasswordComplexitySpecial' => 'N',
                'PasswordComplexityUppercase' => 'N',
                'PasswordComplexityLowercase' => 'N',
            ],
        ]);

        DB::table('Departments')->insert([
            ['ID' => 1, 'OrganizationID' => 1, 'Name' => 'General'],
            ['ID' => 2, 'OrganizationID' => 2, 'Name' => 'Strict Department'],
            ['ID' => 3, 'OrganizationID' => 933, 'Name' => 'SSO Department'],
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
