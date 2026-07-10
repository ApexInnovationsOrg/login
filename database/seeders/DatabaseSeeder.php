<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ReferenceDataSeeder::class);
        $this->call(LocalSamlClientSeeder::class);

        // Known local login: dev@example.test / password.
        // Pinned to seeded Department 1 — letting the factory mint its own
        // department/organization would commit a random faker org name that
        // survives the seed pass and can collide with test-created orgs
        // (Organizations.Name is unique).
        User::factory()->create([
            'Login' => 'dev@example.test',
            'FirstName' => 'Dev',
            'LastName' => 'User',
            'DepartmentID' => 1,
        ]);
    }
}
