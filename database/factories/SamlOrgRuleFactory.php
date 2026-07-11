<?php

namespace Database\Factories;

use App\Models\SamlOrgRule;
use App\Saml\RoutingOperator;
use Illuminate\Database\Eloquent\Factories\Factory;

class SamlOrgRuleFactory extends Factory
{
    protected $model = SamlOrgRule::class;

    public function definition(): array
    {
        return [
            'position' => 1,
            'attribute' => 'department',
            'operator' => RoutingOperator::Equals,
            'value' => $this->faker->unique()->word(),
            'organization_id' => 1,
        ];
    }

    /** The reserved match-everyone triple. */
    public function catchAll(): static
    {
        return $this->state(fn () => [
            'attribute' => '*', 'operator' => RoutingOperator::Wildcard, 'value' => '*',
        ]);
    }
}
