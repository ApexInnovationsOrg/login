<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypesSeeder extends Seeder
{
    /**
     * Seed the EventTypes table used by the admin portal for audit logging.
     */
    public function run(): void
    {
        // Dev fixture; must never touch a shared database. `testing` is
        // included because phpunit runs against a disposable local MySQL
        // database (apex_login_test), not the shared one.
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        DB::table('EventTypes')->insertOrIgnore([
            ['ID' => 1, 'Description' => 'Admin Logged IN'],
            ['ID' => 2, 'Description' => 'Admin Logged OUT'],
        ]);
    }
}
