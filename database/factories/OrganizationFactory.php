<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        // Organizations.CountryID is NOT NULL with a foreign key to Countries;
        // guarantee id 231 (US) exists so a factory-only test needs no seeder.
        DB::table('Countries')->updateOrInsert(
            ['ID' => 231],
            ['Abbreviation' => 'US', 'Name' => 'United States'],
        );

        return [
            'Name' => $this->faker->unique()->company(),
            'Address' => $this->faker->streetAddress(),
            'City' => $this->faker->city(),
            'PostalCode' => $this->faker->postcode(),
            'Phone' => $this->faker->phoneNumber(),
            'CountryID' => 231,
            // StateID is nullable; leave it null (avoids a States dependency).
            'CreationDate' => now()->format('Y-m-d'),
            'PasswordMinLength' => 6,
            'PasswordComplexityNumeric' => 'N',
            'PasswordComplexitySpecial' => 'N',
            'PasswordComplexityUppercase' => 'N',
            'PasswordComplexityLowercase' => 'N',
        ];
    }

    /** The strict-password preset used by organization 2. */
    public function strict(): static
    {
        return $this->state(fn () => [
            'PasswordMinLength' => 12,
            'PasswordComplexityNumeric' => 'Y',
            'PasswordComplexitySpecial' => 'Y',
            'PasswordComplexityUppercase' => 'Y',
            'PasswordComplexityLowercase' => 'Y',
        ]);
    }
}
