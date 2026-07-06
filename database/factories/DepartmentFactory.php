<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'Name' => $this->faker->unique()->word().' Department',
            'Active' => 'Y',
            'OrganizationID' => Organization::factory(),
        ];
    }
}
