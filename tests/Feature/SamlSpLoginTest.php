<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlSpLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
        ]);
    }

    public function test_sp_login_redirects_to_the_idp_with_an_authn_request(): void
    {
        SamlClient::factory()->create([
            'slug' => 'acme',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
        ]);

        $response = $this->get('/saml/acme/login');

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://idp.acme.test/sso?', $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $xml = gzinflate(base64_decode($query['SAMLRequest']));

        $this->assertStringContainsString('AuthnRequest', $xml);
        $this->assertStringContainsString(url('/saml/acme/acs'), $xml);
        $this->assertStringContainsString(url('/saml/acme/metadata'), $xml);
    }

    public function test_unknown_slug_404s(): void
    {
        $this->get('/saml/nope/login')->assertNotFound();
    }

    public function test_disabled_client_404s(): void
    {
        SamlClient::factory()->create(['slug' => 'off', 'enabled' => false]);

        $this->get('/saml/off/login')->assertNotFound();
    }
}
