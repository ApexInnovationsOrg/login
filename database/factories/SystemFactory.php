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
            'Name' => $this->faker->unique()->company().' System',
            'CreationDate' => now()->format('Y-m-d'),
        ];
    }
}
