<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
    }
}
