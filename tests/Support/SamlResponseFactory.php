<?php

namespace Tests\Support;

use OneLogin\Saml2\Utils;

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

        $xml = <<<XML
        <samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
            ID="{$responseId}" Version="2.0" IssueInstant="{$issueInstant}" Destination="{$destination}">
            <saml:Issuer>{$issuer}</saml:Issuer>
            <samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status>
            <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema"
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
        </samlp:Response>
        XML;

        $signed = Utils::addSign(
            $xml,
            file_get_contents($config['signedKeyPath']),
            file_get_contents($config['signedCertPath'])
        );

        return base64_encode($signed);
    }
}
