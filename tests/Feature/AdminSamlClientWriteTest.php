<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AdminSamlClientWriteTest extends TestCase
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

    public function test_create_returns_201_with_detail(): void
    {
        $this->postJson('/api/admin/saml-clients', [
            'name' => 'Acme Hospital',
            'owner_type' => 'organization',
            'owner_id' => 1,
            'email_domains' => ['acme.com'],
        ], $this->headers())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'acme-hospital')
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.email_domains', ['acme.com']);
    }

    public function test_validation_errors_surface_as_422_bag(): void
    {
        SamlClient::factory()->create(['slug' => 'taken']);

        $this->postJson('/api/admin/saml-clients', [
            'name' => 'Other', 'slug' => 'taken', 'owner_id' => 1,
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    }

    public function test_update_patches_fields(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'jit_enabled' => false]);

        $this->patchJson('/api/admin/saml-clients/acme', ['jit_enabled' => true], $this->headers())
            ->assertOk()
            ->assertJsonPath('data.jit_enabled', true);
    }

    public function test_idp_metadata_upload_populates_idp_fields(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'idp_entity_id' => 'pending']);

        $this->postJson('/api/admin/saml-clients/acme/idp-metadata', [
            'xml' => file_get_contents(base_path('tests/Fixtures/saml/okta-idp-metadata.xml')),
        ], $this->headers())
            ->assertOk()
            ->assertJsonPath('data.idp_entity_id', fn ($v) => $v !== 'pending');
    }

    public function test_unparseable_metadata_is_a_422(): void
    {
        SamlClient::factory()->create(['slug' => 'acme']);

        $this->postJson('/api/admin/saml-clients/acme/idp-metadata', ['xml' => 'not xml'],
            $this->headers())->assertStatus(422)->assertJsonValidationErrors('xml');
    }

    public function test_enable_and_disable(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'enabled' => false]);

        $this->postJson('/api/admin/saml-clients/acme/enable', [], $this->headers())
            ->assertOk()->assertJsonPath('data.enabled', true);

        $this->postJson('/api/admin/saml-clients/acme/disable', [], $this->headers())
            ->assertOk()->assertJsonPath('data.enabled', false);
    }

    public function test_audit_fields_reflect_only_submitted_editable_fields(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'jit_enabled' => false]);

        Log::spy();

        $this->patchJson('/api/admin/saml-clients/acme', [
            'jit_enabled' => true,
            'bogus_field' => 'x',
        ], $this->headers())
            ->assertOk();

        Log::shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return isset($context['fields']) &&
                   $context['fields'] === ['jit_enabled'] &&
                   ! array_key_exists('email_domains', $context);
        })->once();
    }

    public function test_api_rejects_domains_on_admin_portal_client(): void
    {
        SamlClient::factory()->adminPortal()->create(['slug' => 'apex-admin']);

        $this->patchJson('/api/admin/saml-clients/apex-admin', ['email_domains' => ['apexinnovations.com']], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('email_domains');
    }
}
