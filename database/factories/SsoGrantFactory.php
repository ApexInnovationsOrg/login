<?php

namespace Database\Factories;

use App\Models\SsoGrant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SsoGrantFactory extends Factory
{
    protected $model = SsoGrant::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'owner_type' => 'organization',
            'owner_id' => 1,
            'granted_by' => '1:Test Admin',
        ];
    }
}
