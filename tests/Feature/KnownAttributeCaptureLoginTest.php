<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Support\SamlResponseFactory;
use Tests\TestCase;

class KnownAttributeCaptureLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private SamlClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
            'saml.replay_store' => 'array',
        ]);

        $this->client = SamlClient::factory()->create([
            'slug' => 'acme',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
            'owner_id' => 933,
            'department_id' => null,
            'jit_enabled' => true,
        ]);
    }

    private function acs(array $responseOverrides = []): TestResponse
    {
        return $this->post('/saml/acme/acs', [
            'SAMLResponse' => SamlResponseFactory::make($responseOverrides),
        ]);
    }

    public function test_login_captures_asserted_attribute_names(): void
    {
        $this->acs(['attributes' => [
            'email' => 'sso.user@acme.test', 'firstName' => 'Sso', 'lastName' => 'User',
            'department' => 'ICU', 'eduPersonAffiliation' => 'staff',
        ]]);

        $known = $this->client->fresh()->known_attributes;
        $this->assertContains('department', $known);
        $this->assertContains('eduPersonAffiliation', $known);
        $this->assertNotContains('email', $known); // identity attribute excluded
        $this->assertDatabaseHas('saml_attribute_observations', ['saml_client_id' => $this->client->id, 'name' => 'department']);
        $this->assertDatabaseMissing('saml_attribute_observations', ['name' => 'ICU']); // never a value
    }
}
