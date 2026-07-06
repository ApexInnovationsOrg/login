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

        // Known local login: dev@example.com / password
        User::factory()->create([
            'Login' => 'dev@example.com',
            'FirstName' => 'Dev',
            'LastName' => 'User',
        ]);
    }
}
