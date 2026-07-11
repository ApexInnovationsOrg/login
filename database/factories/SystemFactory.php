<?php

namespace Database\Factories;

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
}
