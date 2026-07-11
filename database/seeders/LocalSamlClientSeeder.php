<?php

namespace Database\Seeders;

use App\Models\SamlClient;
use Illuminate\Database\Seeder;

class LocalSamlClientSeeder extends Seeder
{
    /**
     * Local mock IdP (kristophjunge/test-saml-idp) — static users user1/user1pass.
     * IdP metadata fields start as 'pending'; `make db` fills them from the
     * running container via `saml:client update --metadata` and enables the client.
     *
     * Verified against a real login round-trip: the image's static `user1`
     * assertion carries plain (non-OID) attribute names `uid`, `email`, and
     * `eduPersonAffiliation` — there is no first/last name attribute at all,
     * so those two keys are intentionally left unmapped and fall back to the
     * controller's literal defaults.
     */
    public function run(): void
    {
        SamlClient::updateOrCreate(['slug' => 'local-idp'], [
            'name' => 'Local Mock IdP',
            'enabled' => false,
            'idp_entity_id' => 'pending',
            'idp_sso_url' => 'pending',
            'idp_certificate' => 'pending',
            'owner_type' => 'organization',
            'owner_id' => 933,
            'department_id' => null,
            'jit_enabled' => true,
            'attribute_map' => [
                'email' => 'email',
            ],
            'email_domains' => ['example.com'],
        ]);

        // Local mock IdP for the *admin portal* SSO flow (second container,
        // mock-idp-admin, because the image only supports one SP ACS URL).
        // user1@example.com is seeded as an Employee by LocalEmployeeSeeder.
        SamlClient::updateOrCreate(['slug' => 'local-admin-idp'], [
            'name' => 'Local Mock IdP (Admin Portal)',
            'enabled' => false,
            'admin_portal' => true,
            'idp_entity_id' => 'pending',
            'idp_sso_url' => 'pending',
            'idp_certificate' => 'pending',
            'owner_type' => 'organization',
            'owner_id' => 933,
            'department_id' => null,
            'jit_enabled' => false,
            'attribute_map' => [
                'email' => 'email',
            ],
        ]);
    }
}
