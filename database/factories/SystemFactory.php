<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\System;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemFactory extends Factory
{
    protected $model = System::class;

    public function definition(): array
    {
        return [
            'Name' => $this->faker->unique()->city().' Health System',
            'CreationDate' => now()->format('Y-m-d'),
        ];
    }

    /**
     * Build the whole tree: N organizations attached through SystemOrganizations,
     * each with departments (int count or exact names — see
     * OrganizationFactory::withDepartments()).
     */
    public function withOrganizations(int $count = 2, int|array $departmentsEach = 3): static
    {
        return $this->has(
            Organization::factory()->count($count)->withDepartments($departmentsEach),
            'organizations',
        );
    }
}
