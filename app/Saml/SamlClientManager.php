<?php

namespace App\Saml;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\System;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        if (isset($input['email_domains'])) {
            $input['email_domains'] = $this->normalizeDomains($input['email_domains']);
        }

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:saml_clients,slug'],
            'owner_type' => ['required', 'in:organization,system'],
            'owner_id' => ['required', 'integer', 'min:1'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'jit_enabled' => ['sometimes', 'boolean'],
            'admin_portal' => ['sometimes', 'boolean'],
            'attribute_map' => ['sometimes', 'array'],
            'attribute_map.email' => ['required_with:attribute_map', 'string'],
            'attribute_map.first_name' => ['sometimes', 'string'],
            'attribute_map.last_name' => ['sometimes', 'string'],
            'email_domains' => ['sometimes', 'array'],
            'email_domains.*' => ['string', 'regex:/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/'],
        ])->validate();

        $this->assertOwnerExists($validated['owner_type'], $validated['owner_id']);
        $this->assertDefaultDepartmentValid(
            $validated['owner_type'],
            $validated['owner_id'],
            $validated['department_id'] ?? null,
        );

        $this->assertDomainsUnclaimed($validated['email_domains'] ?? [], null);

        $this->assertAdminPortalHoldsNoDomains(
            (bool) ($validated['admin_portal'] ?? false),
            $validated['email_domains'] ?? [],
        );

        return SamlClient::create($validated + [
            'enabled' => false, // enabled explicitly once IdP metadata is in place
            'jit_enabled' => false,
            'idp_entity_id' => 'pending',
            'idp_sso_url' => 'pending',
            'idp_certificate' => 'pending',
            'attribute_map' => self::DEFAULT_ATTRIBUTE_MAP,
            'email_domains' => [],
        ]);
    }

    public function update(SamlClient $client, array $input): SamlClient
    {
        if (array_key_exists('owner_type', $input) !== array_key_exists('owner_id', $input)) {
            throw ValidationException::withMessages([
                'owner_id' => 'Re-parenting requires owner_type and owner_id together.',
            ]);
        }

        if (isset($input['email_domains'])) {
            $input['email_domains'] = $this->normalizeDomains($input['email_domains']);
        }

        $validated = Validator::make($input, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:64', 'alpha_dash', 'unique:saml_clients,slug,'.$client->id],
            'owner_type' => ['sometimes', 'required', 'in:organization,system'],
            'owner_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'jit_enabled' => ['sometimes', 'boolean'],
            'admin_portal' => ['sometimes', 'boolean'],
            'attribute_map' => ['sometimes', 'array'],
            'attribute_map.email' => ['required_with:attribute_map', 'string'],
            'attribute_map.first_name' => ['sometimes', 'string'],
            'attribute_map.last_name' => ['sometimes', 'string'],
            'email_domains' => ['sometimes', 'array'],
            'email_domains.*' => ['string', 'regex:/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/'],
        ])->validate();

        $this->assertOwnerExists(
            $validated['owner_type'] ?? $client->owner_type,
            $validated['owner_id'] ?? $client->owner_id,
        );
        $this->assertDefaultDepartmentValid(
            $validated['owner_type'] ?? $client->owner_type,
            $validated['owner_id'] ?? $client->owner_id,
            array_key_exists('department_id', $validated) ? $validated['department_id'] : $client->department_id,
        );

        $this->assertDomainsUnclaimed($validated['email_domains'] ?? [], $client);

        $this->assertAdminPortalHoldsNoDomains(
            (bool) ($validated['admin_portal'] ?? $client->admin_portal),
            $validated['email_domains'] ?? ($client->email_domains ?? []),
        );

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

    /**
     * @param  array<int, mixed>  $domains
     * @return array<int, string>
     */
    private function normalizeDomains(array $domains): array
    {
        return array_values(array_unique(array_map(
            fn ($domain) => strtolower(ltrim(trim((string) $domain), '@')),
            $domains,
        )));
    }

    /**
     * A domain may belong to at most one client, enabled or not — a disabled
     * client's claim still blocks, so re-enabling never creates a conflict.
     */
    private function assertDomainsUnclaimed(array $domains, ?SamlClient $except = null): void
    {
        foreach ($domains as $domain) {
            $claimed = SamlClient::whereJsonContains('email_domains', $domain)
                ->when($except, fn ($query) => $query->where('id', '!=', $except->id))
                ->exists();

            if ($claimed) {
                throw ValidationException::withMessages([
                    'email_domains' => "Domain {$domain} is already claimed by another SAML client.",
                ]);
            }
        }
    }

    /**
     * Admin-portal clients assert Employee identities and never participate
     * in customer email-domain routing (spec: admin portal SSO).
     */
    private function assertAdminPortalHoldsNoDomains(bool $adminPortal, array $domains): void
    {
        if ($adminPortal && $domains !== []) {
            throw ValidationException::withMessages([
                'email_domains' => 'An admin-portal client cannot claim email domains.',
            ]);
        }
    }

    private function assertOwnerExists(string $ownerType, int $ownerId): void
    {
        $exists = $ownerType === 'organization'
            ? Organization::where('ID', $ownerId)->exists()
            : System::where('ID', $ownerId)->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'owner_id' => "No {$ownerType} with ID {$ownerId}.",
            ]);
        }
    }

    /**
     * A default department only makes sense on an org-owned client and must
     * belong to the owning organization (closes the milestone-4 gap where
     * department_id was never validated).
     */
    private function assertDefaultDepartmentValid(string $ownerType, int $ownerId, ?int $departmentId): void
    {
        if ($departmentId === null) {
            return;
        }

        if ($ownerType !== 'organization') {
            throw ValidationException::withMessages([
                'department_id' => 'A default department requires an organization-owned client.',
            ]);
        }

        $belongs = Department::where('ID', $departmentId)
            ->where('OrganizationID', $ownerId)
            ->exists();

        if (! $belongs) {
            throw ValidationException::withMessages([
                'department_id' => 'Department does not belong to the owning organization.',
            ]);
        }
    }
}
