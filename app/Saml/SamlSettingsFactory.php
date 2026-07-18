<?php

namespace App\Saml;

use App\Models\SamlClient;

class SamlSettingsFactory
{
    /**
     * Build a php-saml settings array for one client.
     * Pure: reads only the client row and config.
     */
    public function forClient(SamlClient $client): array
    {
        return [
            'strict' => true,
            'sp' => [
                // Entity ID == metadata URL, per convention. Per-client because
                // Entra enforces Identifier uniqueness per tenant, and it scopes
                // the audience restriction to the one client the assertion is for.
                'entityId' => $client->metadataUrl(),
                'assertionConsumerService' => [
                    'url' => $client->acsUrl(),
                ],
                'x509cert' => $this->pem(config('saml.sp.cert_path')),
                'privateKey' => $this->pem(config('saml.sp.key_path')),
            ],
            'idp' => [
                'entityId' => $client->idp_entity_id,
                'singleSignOnService' => [
                    'url' => $client->idp_sso_url,
                ],
                'x509cert' => $client->idp_certificate,
            ],
            'security' => [
                'wantAssertionsSigned' => true,
                'wantXMLValidation' => true,
            ],
        ];
    }

    private function pem(?string $path): string
    {
        return ($path && is_file($path)) ? file_get_contents($path) : '';
    }
}
