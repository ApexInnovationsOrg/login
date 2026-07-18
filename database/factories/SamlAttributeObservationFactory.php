<?php

namespace Database\Factories;

use App\Models\SamlAttributeObservation;
use App\Models\SamlClient;
use Illuminate\Database\Eloquent\Factories\Factory;

class SamlAttributeObservationFactory extends Factory
{
    protected $model = SamlAttributeObservation::class;

    public function definition(): array
    {
        return [
            'saml_client_id' => SamlClient::factory(),
            'name' => $this->faker->unique()->word(),
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'observation_count' => 1,
        ];
    }
}
