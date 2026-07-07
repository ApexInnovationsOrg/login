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
        DB::table('EventTypes')->insertOrIgnore([
            ['ID' => 1, 'Description' => 'Admin Logged IN'],
            ['ID' => 2, 'Description' => 'Admin Logged OUT'],
        ]);
    }
}
