<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use App\Saml\SamlClientManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SamlClientManagerTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): SamlClientManager
    {
        return app(SamlClientManager::class);
    }

    public function test_create_with_minimal_input_slugs_the_name(): void
    {
        $client = $this->manager()->create([
            'name' => 'Health System One',
            'organization_id' => 1,
        ]);

        $this->assertSame('health-system-one', $client->slug);
        $this->assertFalse($client->enabled); // disabled until IdP metadata arrives
        $this->assertFalse($client->jit_enabled);
        $this->assertSame('pending', $client->idp_entity_id);
        $this->assertArrayHasKey('email', $client->attribute_map);
    }

    public function test_create_validates_required_fields(): void
    {
        $this->expectException(ValidationException::class);

        $this->manager()->create(['name' => '']);
    }

    public function test_create_rejects_duplicate_slug(): void
    {
        SamlClient::factory()->create(['slug' => 'acme']);

        $this->expectException(ValidationException::class);

        $this->manager()->create(['name' => 'Acme', 'slug' => 'acme', 'organization_id' => 1]);
    }

    public function test_update_from_idp_metadata_fills_idp_fields(): void
    {
        $client = SamlClient::factory()->create();
        $xml = file_get_contents(base_path('tests/Fixtures/saml/okta-idp-metadata.xml'));

        $client = $this->manager()->updateFromIdpMetadata($client, $xml);

        $this->assertSame('http://www.okta.com/exk1fixture0Okta', $client->idp_entity_id);
        $this->assertStringContainsString('MIIFIXTUREOKTACERTBODY', $client->idp_certificate);
    }

    public function test_set_enabled_toggles(): void
    {
        $client = SamlClient::factory()->create(['enabled' => false]);

        $this->assertTrue($this->manager()->setEnabled($client, true)->enabled);
        $this->assertFalse($this->manager()->setEnabled($client, false)->enabled);
    }

    public function test_certificate_status_reads_expiry(): void
    {
        // sp.crt fixture is valid for 3650 days from generation — not expiring
        $client = SamlClient::factory()->create([
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/sp.crt')),
        ]);

        $status = $this->manager()->certificateStatus($client);

        $this->assertNotNull($status['expires_at']);
        $this->assertFalse($status['expiring']);
    }

    public function test_certificate_status_handles_placeholder(): void
    {
        $client = SamlClient::factory()->create(['idp_certificate' => 'pending']);

        $status = $this->manager()->certificateStatus($client);

        $this->assertNull($status['expires_at']);
        $this->assertFalse($status['expiring']);
    }
}
