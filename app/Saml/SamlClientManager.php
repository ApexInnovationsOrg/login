<?php

namespace App\Saml;

use InvalidArgumentException;
use OneLogin\Saml2\IdPMetadataParser;

class SamlClientManager
{
    /**
     * Extract entity ID, SSO URL, and signing certificate from IdP metadata XML.
     *
     * @return array{idp_entity_id: string, idp_sso_url: string, idp_certificate: string}
     */
    public function parseIdpMetadata(string $xml): array
    {
        try {
            $parsed = IdPMetadataParser::parseXML($xml);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Could not parse IdP metadata: '.$e->getMessage(), previous: $e);
        }

        $idp = $parsed['idp'] ?? [];
        $certificate = $idp['x509cert']
            ?? ($idp['x509certMulti']['signing'][0] ?? null);

        if (empty($idp['entityId']) || empty($idp['singleSignOnService']['url']) || empty($certificate)) {
            throw new InvalidArgumentException('IdP metadata is missing entity ID, SSO URL, or signing certificate.');
        }

        return [
            'idp_entity_id' => $idp['entityId'],
            'idp_sso_url' => $idp['singleSignOnService']['url'],
            'idp_certificate' => $certificate,
        ];
    }
}
