<?php

namespace Database\Factories;

use App\Models\SamlDepartmentRule;
use App\Saml\RoutingOperator;
use Illuminate\Database\Eloquent\Factories\Factory;

class SamlDepartmentRuleFactory extends Factory
{
    protected $model = SamlDepartmentRule::class;

    public function definition(): array
    {
        return [
            'position' => 1,
            'attribute' => 'department',
            'operator' => RoutingOperator::Equals,
            'value' => $this->faker->unique()->word(),
            'department_name' => $this->faker->unique()->word(),
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
