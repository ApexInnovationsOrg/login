<?php

namespace Tests\Feature;

use App\Models\SamlAttributeObservation;
use App\Models\SamlClient;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminApi;
use Tests\TestCase;

class AdminSamlClientReadTest extends TestCase
{
    use InteractsWithAdminApi;
    use RefreshDatabase;

    protected $seed = true;

    private function headers(): array
    {
        return $this->adminApiHeaders();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureAdminApi();
    }

    public function test_index_lists_clients_with_certificate_status(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'email_domains' => ['acme.com']]);

        $this->getJson('/api/admin/saml-clients', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.0.slug', fn ($v) => is_string($v))
            ->assertJsonStructure(['data' => [['name', 'slug', 'enabled', 'jit_enabled',
                'owner' => ['type', 'id', 'name'], 'department_id', 'email_domains',
                'certificate' => ['expires_at', 'expiring']]]]);
    }

    public function test_show_returns_full_detail(): void
    {
        $client = SamlClient::factory()->create(['slug' => 'acme']);

        $this->getJson('/api/admin/saml-clients/acme', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.slug', 'acme')
            ->assertJsonPath('data.acs_url', $client->acsUrl())
            ->assertJsonStructure(['data' => ['acs_url', 'metadata_url', 'idp_entity_id',
                'idp_sso_url', 'attribute_map', 'owner' => ['type', 'id', 'name'], 'grants_count']]);
    }

    public function test_unknown_slug_404s(): void
    {
        $this->getJson('/api/admin/saml-clients/nope', $this->headers())->assertNotFound();
    }

    public function test_detail_includes_admin_portal_flag(): void
    {
        SamlClient::factory()->adminPortal()->create(['slug' => 'apex-admin']);

        $this->getJson('/api/admin/saml-clients/apex-admin', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.admin_portal', true);
    }

    public function test_detail_shows_system_owner(): void
    {
        $system = System::factory()->create(['Name' => 'Mercy Health System']);
        SamlClient::factory()->forSystem($system->ID)->create(['slug' => 'sys', 'department_id' => null]);

        $this->getJson('/api/admin/saml-clients/sys', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.owner.type', 'system')
            ->assertJsonPath('data.owner.id', $system->ID)
            ->assertJsonPath('data.owner.name', 'Mercy Health System');
    }

    public function test_detail_includes_known_attributes_and_observations(): void
    {
        $client = SamlClient::factory()->create(['slug' => 'acme', 'known_attributes' => ['department']]);
        SamlAttributeObservation::factory()->create([
            'saml_client_id' => $client->id, 'name' => 'department', 'last_seen_at' => now(),
        ]);

        $this->getJson('/api/admin/saml-clients/acme', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.known_attributes', ['department'])
            ->assertJsonPath('data.attribute_observations.0.name', 'department');
    }
}
