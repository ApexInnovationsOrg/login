<?php

namespace Tests\Unit;

use App\Models\SamlClient;
use App\Saml\SamlSettingsFactory;
use OneLogin\Saml2\Settings;
use Tests\TestCase;

class SamlSettingsFactoryTest extends TestCase
{
    private function client(): SamlClient
    {
        return new SamlClient([
            'slug' => 'acme',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/sp.crt')),
        ]);
    }

    public function test_builds_settings_php_saml_accepts(): void
    {
        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
        ]);

        $settings = new Settings(app(SamlSettingsFactory::class)->forClient($this->client()));

        $this->assertSame('https://idp.acme.test/metadata', $settings->getIdPData()['entityId']);
        $this->assertStringEndsWith('/saml/acme/acs', $settings->getSPData()['assertionConsumerService']['url']);
        $this->assertTrue($settings->getSecurityData()['wantAssertionsSigned']);
    }

    public function test_missing_sp_keypair_yields_empty_strings(): void
    {
        config([
            'saml.sp.cert_path' => '/nonexistent/sp.crt',
            'saml.sp.key_path' => '/nonexistent/sp.key',
        ]);

        $array = app(SamlSettingsFactory::class)->forClient($this->client());

        $this->assertSame('', $array['sp']['x509cert']);
        $this->assertSame('', $array['sp']['privateKey']);
    }
}
