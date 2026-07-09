<?php

namespace Tests\Unit;

use App\Saml\SamlClientManager;
use Tests\TestCase;

class SamlMetadataParserTest extends TestCase
{
    public function test_parses_okta_metadata(): void
    {
        $xml = file_get_contents(base_path('tests/Fixtures/saml/okta-idp-metadata.xml'));

        $parsed = app(SamlClientManager::class)->parseIdpMetadata($xml);

        $this->assertSame('http://www.okta.com/exk1fixture0Okta', $parsed['idp_entity_id']);
        $this->assertSame('https://dev-1234.okta.com/app/exk1fixture0Okta/sso/saml', $parsed['idp_sso_url']);
        $this->assertStringContainsString('MIIFIXTUREOKTACERTBODY', $parsed['idp_certificate']);
    }

    public function test_parses_entra_metadata(): void
    {
        $xml = file_get_contents(base_path('tests/Fixtures/saml/entra-idp-metadata.xml'));

        $parsed = app(SamlClientManager::class)->parseIdpMetadata($xml);

        $this->assertSame('https://sts.windows.net/fixture-tenant-guid/', $parsed['idp_entity_id']);
        $this->assertSame('https://login.microsoftonline.com/fixture-tenant-guid/saml2', $parsed['idp_sso_url']);
        $this->assertStringContainsString('MIIFIXTUREENTRACERTBODY', $parsed['idp_certificate']);
    }

    public function test_rejects_garbage(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(SamlClientManager::class)->parseIdpMetadata('<not-saml/>');
    }
}
