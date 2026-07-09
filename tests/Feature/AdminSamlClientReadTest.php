<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSamlClientReadTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function headers(): array
    {
        return ['Authorization' => 'Bearer test-token', 'X-Acting-Admin' => '1:Test Admin'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin.api_token' => 'test-token']);
    }

    public function test_index_lists_clients_with_certificate_status(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'email_domains' => ['acme.com']]);

        $this->getJson('/api/admin/saml-clients', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.0.slug', fn ($v) => is_string($v))
            ->assertJsonStructure(['data' => [['name', 'slug', 'enabled', 'jit_enabled',
                'organization_id', 'department_id', 'email_domains',
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
                'idp_sso_url', 'attribute_map', 'organization_name', 'grants_count']]);
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
}
