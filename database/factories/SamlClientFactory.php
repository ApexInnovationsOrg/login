<?php

namespace Database\Factories;

use App\Models\SamlClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SamlClientFactory extends Factory
{
    protected $model = SamlClient::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'enabled' => true,
            'idp_entity_id' => 'https://idp.example.com/'.Str::slug($name),
            'idp_sso_url' => 'https://idp.example.com/'.Str::slug($name).'/sso',
            'idp_certificate' => 'MIIC-placeholder-not-a-real-cert',
            'owner_type' => 'organization',
            'owner_id' => 1,
            'department_id' => null,
            'jit_enabled' => true,
            'admin_portal' => false,
            'attribute_map' => [
                'email' => 'email',
                'first_name' => 'firstName',
                'last_name' => 'lastName',
            ],
            'email_domains' => [],
        ];
    }

    /** A client asserting Employee (admin portal) identities. */
    public function adminPortal(): static
    {
        return $this->state(fn () => ['admin_portal' => true]);
    }

    /** Owned by a system: spans that system's organizations. */
    public function forSystem(int $systemId): static
    {
        return $this->state(fn () => ['owner_type' => 'system', 'owner_id' => $systemId]);
    }
}
