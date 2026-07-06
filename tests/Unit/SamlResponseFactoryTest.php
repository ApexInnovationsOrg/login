<?php

namespace Tests\Unit;

use Tests\Support\SamlResponseFactory;
use Tests\TestCase;

class SamlResponseFactoryTest extends TestCase
{
    public function test_produces_base64_signed_response(): void
    {
        $encoded = SamlResponseFactory::make();
        $xml = base64_decode($encoded, true);

        $this->assertNotFalse($xml);
        $this->assertStringContainsString('<samlp:Response', $xml);
        $this->assertStringContainsString('SignatureValue', $xml);
        $this->assertStringContainsString('sso.user@acme.test', $xml);
    }

    public function test_attribute_context_overrides_are_quote_safe(): void
    {
        $xml = base64_decode(SamlResponseFactory::make([
            'destination' => 'https://sp.test/acs?a="b"',
            'assertionId' => '_id"with"quotes',
        ]), true);

        $doc = new \DOMDocument;
        $this->assertTrue($doc->loadXML($xml));
    }
}
