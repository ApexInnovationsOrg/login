<?php

namespace Tests\Support;

use DOMDocument;
use OneLogin\Saml2\Utils;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SamlResponseFactory
{
    /**
     * Build a base64-encoded signed SAMLResponse for feature tests.
     */
    public static function make(array $overrides = []): string
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $config = $overrides + [
            'destination' => url('/saml/acme/acs'),
            'audience' => config('saml.sp.entity_id'),
            'issuer' => 'https://idp.acme.test/metadata',
            'nameId' => 'sso.user@acme.test',
            'attributes' => [
                'email' => 'sso.user@acme.test',
                'firstName' => 'Sso',
                'lastName' => 'User',
            ],
            'assertionId' => '_'.bin2hex(random_bytes(16)),
            'notOnOrAfter' => $now->add(new \DateInterval('PT5M')),
            'signedKeyPath' => base_path('tests/Fixtures/saml/idp.key'),
            'signedCertPath' => base_path('tests/Fixtures/saml/idp.crt'),
        ];

        $issueInstant = $now->format('Y-m-d\TH:i:s\Z');
        $notOnOrAfter = \DateTimeImmutable::createFromInterface($config['notOnOrAfter'])
            ->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
        $notBefore = $now->sub(new \DateInterval('PT5M'))->format('Y-m-d\TH:i:s\Z');
        $responseId = '_'.bin2hex(random_bytes(16));

        $attributeXml = '';
        foreach ($config['attributes'] as $name => $value) {
            $name = htmlspecialchars($name, ENT_XML1 | ENT_QUOTES);
            $value = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES);
            $attributeXml .= <<<XML
                <saml:Attribute Name="{$name}" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified">
                    <saml:AttributeValue xsi:type="xs:string">{$value}</saml:AttributeValue>
                </saml:Attribute>
            XML;
        }

        $destination = htmlspecialchars($config['destination'], ENT_XML1 | ENT_QUOTES);
        $audience = htmlspecialchars($config['audience'], ENT_XML1 | ENT_QUOTES);
        $issuer = htmlspecialchars($config['issuer'], ENT_XML1 | ENT_QUOTES);
        $nameId = htmlspecialchars($config['nameId'], ENT_XML1 | ENT_QUOTES);
        $assertionId = htmlspecialchars($config['assertionId'], ENT_XML1 | ENT_QUOTES);

        // The Assertion is built (and signed) as its own document, because php-saml's
        // default wantAssertionsSigned=true validates the signature over the Assertion
        // node specifically, not the Response root.
        $assertionXml = <<<XML
        <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema"
            ID="{$assertionId}" Version="2.0" IssueInstant="{$issueInstant}">
            <saml:Issuer>{$issuer}</saml:Issuer>
            <saml:Subject>
                <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress">{$nameId}</saml:NameID>
                <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                    <saml:SubjectConfirmationData NotOnOrAfter="{$notOnOrAfter}" Recipient="{$destination}"/>
                </saml:SubjectConfirmation>
            </saml:Subject>
            <saml:Conditions NotBefore="{$notBefore}" NotOnOrAfter="{$notOnOrAfter}">
                <saml:AudienceRestriction><saml:Audience>{$audience}</saml:Audience></saml:AudienceRestriction>
            </saml:Conditions>
            <saml:AuthnStatement AuthnInstant="{$issueInstant}" SessionIndex="{$assertionId}">
                <saml:AuthnContext>
                    <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
                </saml:AuthnContext>
            </saml:AuthnStatement>
            <saml:AttributeStatement>{$attributeXml}</saml:AttributeStatement>
        </saml:Assertion>
        XML;

        $signedAssertion = self::signAssertion(
            $assertionXml,
            file_get_contents($config['signedKeyPath']),
            file_get_contents($config['signedCertPath'])
        );

        $xml = <<<XML
        <samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
            ID="{$responseId}" Version="2.0" IssueInstant="{$issueInstant}" Destination="{$destination}">
            <saml:Issuer>{$issuer}</saml:Issuer>
            <samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status>
            {$signedAssertion}
        </samlp:Response>
        XML;

        return base64_encode($xml);
    }

    /**
     * Sign a SAML Assertion in place, keeping its existing ID attribute (unlike
     * OneLogin\Saml2\Utils::addSign, which always mints a fresh "pfx..." ID) and
     * inserting the resulting <ds:Signature> immediately after <saml:Issuer>, as
     * required by the SAML schema (Utils::addSign only knows how to place the
     * signature correctly for AuthnRequest/Response/LogoutRequest/LogoutResponse).
     */
    private static function signAssertion(string $xml, string $key, string $cert): string
    {
        $dom = new DOMDocument;
        $dom->loadXML($xml);

        $assertionNode = $dom->firstChild;
        $assertionId = $assertionNode->getAttribute('ID');

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($key, false);

        $objXMLSecDSig = new XMLSecurityDSig;
        $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        $objXMLSecDSig->addReferenceList(
            [$assertionNode],
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N],
            ['id_name' => 'ID', 'overwrite' => false]
        );

        $objXMLSecDSig->sign($objKey);
        $objXMLSecDSig->add509Cert($cert, true);

        $issuerNodes = Utils::query($dom, '/saml:Assertion/saml:Issuer');
        $insertBefore = $issuerNodes->length === 1 ? $issuerNodes->item(0)->nextSibling : $assertionNode->firstChild;

        $objXMLSecDSig->insertSignature($assertionNode, $insertBefore);

        // Sanity check: signing must not have changed the Assertion's ID.
        assert($assertionNode->getAttribute('ID') === $assertionId);

        return $dom->saveXML($assertionNode);
    }
}
