<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
        ]);
    }

    public function test_serves_sp_metadata_for_client(): void
    {
        SamlClient::factory()->create(['slug' => 'acme']);

        $response = $this->get('/saml/acme/metadata');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/xml; charset=UTF-8');
        $response->assertSee('AssertionConsumerService', false);
        $response->assertSee('/saml/acme/acs', false);
        $response->assertSee('entityID="'.url('/saml/acme/metadata').'"', false);
    }

    public function test_unknown_client_404s(): void
    {
        $this->get('/saml/nope/metadata')->assertNotFound();
    }
}
