<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocalEmployeeSeeder extends Seeder
{
    /**
     * Known local admin for the legacy portal (website_root/admin):
     * dev.admin / password. The portal hashes with md5('p6^8&'.$pw)
     * (website_admin/doLogon.php) and force-resets passwords older than
     * 90 days (HEADER.php), hence the fresh PasswordLastChanged.
     */
    public function run(): void
    {
        // Dev fixture; must never touch a shared database. `testing` is
        // included because phpunit runs against a disposable local MySQL
        // database (apex_login_test), not the shared one.
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        DB::table('Employees')->updateOrInsert(
            ['Email' => 'dev.admin@apexinnovations.com'],
            [
                'FirstName' => 'Dev',
                'LastName' => 'Admin',
                'Password' => md5('p6^8&password'),
                'Active' => 'Y',
                'PasswordLastChanged' => now()->format('Y-m-d H:i:s'),
            ],
        );

        // The mock IdP's static user1 assertion (email user1@example.com),
        // seeded as an active Employee so admin-portal SSO can match it.
        DB::table('Employees')->updateOrInsert(
            ['Email' => 'user1@example.com'],
            [
                'FirstName' => 'Mock',
                'LastName' => 'IdPAdmin',
                'Password' => md5('p6^8&'.Str::random(32)), // never used; SSO only
                'Active' => 'Y',
                'PasswordLastChanged' => now()->format('Y-m-d H:i:s'),
            ],
        );
    }
}
