<?php

namespace App\Saml;

use App\Models\SamlClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use OneLogin\Saml2\IdPMetadataParser;
use OneLogin\Saml2\Utils;

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

    private const DEFAULT_ATTRIBUTE_MAP = [
        'email' => 'email',
        'first_name' => 'firstName',
        'last_name' => 'lastName',
    ];

    public function create(array $input): SamlClient
    {
        $input['slug'] = $input['slug'] ?? Str::slug($input['name'] ?? '');

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:saml_clients,slug'],
            'organization_id' => ['required', 'integer', 'min:1'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'jit_enabled' => ['sometimes', 'boolean'],
            'attribute_map' => ['sometimes', 'array'],
            'attribute_map.email' => ['required_with:attribute_map', 'string'],
        ])->validate();

        return SamlClient::create($validated + [
            'enabled' => false, // enabled explicitly once IdP metadata is in place
            'jit_enabled' => false,
            'idp_entity_id' => 'pending',
            'idp_sso_url' => 'pending',
            'idp_certificate' => 'pending',
            'attribute_map' => self::DEFAULT_ATTRIBUTE_MAP,
        ]);
    }

    public function update(SamlClient $client, array $input): SamlClient
    {
        $validated = Validator::make($input, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:64', 'alpha_dash', 'unique:saml_clients,slug,'.$client->id],
            'organization_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'jit_enabled' => ['sometimes', 'boolean'],
            'attribute_map' => ['sometimes', 'array'],
            'attribute_map.email' => ['required_with:attribute_map', 'string'],
        ])->validate();

        $client->update($validated);

        return $client->refresh();
    }

    public function updateFromIdpMetadata(SamlClient $client, string $xml): SamlClient
    {
        $client->update($this->parseIdpMetadata($xml));

        return $client->refresh();
    }

    public function setEnabled(SamlClient $client, bool $enabled): SamlClient
    {
        $client->update(['enabled' => $enabled]);

        return $client->refresh();
    }

    /**
     * @return array{expires_at: ?CarbonImmutable, expiring: bool}
     */
    public function certificateStatus(SamlClient $client): array
    {
        $pem = Utils::formatCert($client->idp_certificate);
        $parsed = @openssl_x509_parse(@openssl_x509_read($pem) ?: '');

        if (! $parsed || empty($parsed['validTo_time_t'])) {
            return ['expires_at' => null, 'expiring' => false];
        }

        $expires = CarbonImmutable::createFromTimestamp($parsed['validTo_time_t']);

        return [
            'expires_at' => $expires,
            'expiring' => $expires->isBefore(now()->addDays(30)),
        ];
    }
}
