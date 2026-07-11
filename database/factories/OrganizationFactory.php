<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\System;
use App\Models\SystemOrganization;
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
            // City carries the uniqueness (Organizations.Name is globally
            // unique); the suffix carries the healthcare flavor.
            'Name' => $this->faker->unique()->city().' '.$this->faker->randomElement([
                'Medical Center', 'Regional Hospital', 'Community Hospital',
                'Memorial Hospital', 'General Hospital',
            ]),
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

    /**
     * Attach the org to a system via SystemOrganizations. A string looks up a
     * System by Name or creates it (so repeated calls share one system); no
     * argument creates a fresh system. Resolution is deferred to afterCreating
     * so the lookup happens at create() time, and the join row is keyed on the
     * org — enforcing the one-system-per-org rule the DB doesn't.
     */
    public function forSystem(System|string|null $system = null): static
    {
        return $this->afterCreating(function (Organization $org) use ($system) {
            $resolved = match (true) {
                $system instanceof System => $system,
                is_string($system) => System::where('Name', $system)->first()
                    ?? System::factory()->create(['Name' => $system]),
                default => System::factory()->create(),
            };

            SystemOrganization::updateOrCreate(
                ['OrganizationID' => $org->ID],
                ['SystemID' => $resolved->ID],
            );
        });
    }
}
