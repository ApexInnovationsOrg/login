<?php

namespace App\Saml;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Models\System;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use OneLogin\Saml2\IdPMetadataParser;
use OneLogin\Saml2\Utils;

class SamlClientManager
{
    /** Field names this manager's validators accept — the audit layer reports exactly these. */
    public const EDITABLE_FIELDS = ['name', 'slug', 'owner_type', 'owner_id', 'department_id', 'jit_enabled', 'admin_portal', 'email_domains', 'attribute_map'];

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

        $validated = Validator::make($input, $this->rules(forUpdate: false))->validate();

        $this->assertOwnerAndDepartmentValid($validated, null);
        $this->assertDomainInvariants($validated, null);

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

        $validated = Validator::make($input, $this->rules(forUpdate: true, client: $client))->validate();

        $this->assertOwnerAndDepartmentValid($validated, $client);
        $this->assertNoRoutingRulesWhenReparenting($client, $validated);
        $this->assertDomainInvariants($validated, $client);

        $client->update($validated);

        return $client->refresh();
    }

    /**
     * Validation rules for create() and update() — identical except that
     * update() makes name/owner_type/owner_id optional (`sometimes`) and
     * scopes the slug uniqueness check to exclude the client being updated.
     *
     * @return array<string, mixed>
     */
    private function rules(bool $forUpdate, ?SamlClient $client = null): array
    {
        $slugUnique = $forUpdate
            ? 'unique:saml_clients,slug,'.$client->id
            : 'unique:saml_clients,slug';

        return [
            'name' => $forUpdate ? ['sometimes', 'required', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'slug' => array_merge(
                $forUpdate ? ['sometimes', 'required'] : ['required'],
                ['string', 'max:64', 'alpha_dash', $slugUnique],
            ),
            'owner_type' => $forUpdate ? ['sometimes', 'required', 'in:organization,system'] : ['required', 'in:organization,system'],
            'owner_id' => $forUpdate ? ['sometimes', 'required', 'integer', 'min:1'] : ['required', 'integer', 'min:1'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'jit_enabled' => ['sometimes', 'boolean'],
            'admin_portal' => ['sometimes', 'boolean'],
            'attribute_map' => ['sometimes', 'array'],
            'attribute_map.email' => ['required_with:attribute_map', 'string'],
            'attribute_map.first_name' => ['sometimes', 'string'],
            'attribute_map.last_name' => ['sometimes', 'string'],
            'email_domains' => ['sometimes', 'array'],
            'email_domains.*' => ['string', 'regex:/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/'],
        ];
    }

    /**
     * First two of the four post-validation asserts shared by create() and
     * update(): owner exists, and the default department (if any) is valid
     * for that owner. On create $existing is null, so every fallback
     * resolves to the request's own defaults; on update, an omitted field
     * falls back to the existing client's current value.
     *
     * @param  array<string, mixed>  $validated
     */
    private function assertOwnerAndDepartmentValid(array $validated, ?SamlClient $existing): void
    {
        $ownerType = $validated['owner_type'] ?? $existing?->owner_type;
        $ownerId = $validated['owner_id'] ?? $existing?->owner_id;

        $this->assertOwnerExists($ownerType, $ownerId);

        $departmentId = $existing !== null && ! array_key_exists('department_id', $validated)
            ? $existing->department_id
            : ($validated['department_id'] ?? null);

        $this->assertDefaultDepartmentValid($ownerType, $ownerId, $departmentId);
    }

    /**
     * Last two of the four shared asserts: the requested domains aren't
     * claimed elsewhere, and admin-portal clients hold no domains. Same
     * $existing/fallback contract as assertOwnerAndDepartmentValid().
     *
     * @param  array<string, mixed>  $validated
     */
    private function assertDomainInvariants(array $validated, ?SamlClient $existing): void
    {
        $this->assertDomainsUnclaimed($validated['email_domains'] ?? [], $existing);

        $this->assertAdminPortalHoldsNoDomains(
            (bool) ($validated['admin_portal'] ?? $existing?->admin_portal ?? false),
            $validated['email_domains'] ?? ($existing?->email_domains ?? []),
        );
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
     * Replace both ordered rule lists wholesale. Validates everything —
     * shape, owner-kind, scope membership, catch-all placement — before any
     * write; positions are derived from array order. Audit logging is the
     * API layer's job (Task 6), not the manager's.
     *
     * @param  array<int, array<string, mixed>>  $orgRules
     * @param  array<int, array<string, mixed>>  $departmentRules
     */
    public function replaceRoutingRules(SamlClient $client, array $orgRules, array $departmentRules): SamlClient
    {
        if ($orgRules !== [] && $client->ownedByOrganization()) {
            throw ValidationException::withMessages([
                'org_rules' => 'Organization rules require a system-owned client.',
            ]);
        }

        Validator::make(
            ['org_rules' => $orgRules, 'department_rules' => $departmentRules],
            [
                'org_rules' => ['array'],
                'org_rules.*.attribute' => ['required', 'string'],
                'org_rules.*.operator' => ['required', Rule::enum(RoutingOperator::class)],
                'org_rules.*.value' => ['required', 'string'],
                'org_rules.*.organization_id' => ['required', 'integer'],
                'department_rules' => ['array'],
                'department_rules.*.attribute' => ['required', 'string'],
                'department_rules.*.operator' => ['required', Rule::enum(RoutingOperator::class)],
                'department_rules.*.value' => ['required', 'string'],
                'department_rules.*.department_name' => ['required', 'string'],
            ]
        )->validate();

        $scope = $client->scopedOrganizationIds();

        foreach ($orgRules as $index => $rule) {
            if (! in_array((int) $rule['organization_id'], $scope, true)) {
                throw ValidationException::withMessages([
                    "org_rules.{$index}.organization_id" => "Organization is outside this client's scope.",
                ]);
            }
        }

        $this->assertCatchAllPlacement($orgRules, 'org_rules');
        $this->assertCatchAllPlacement($departmentRules, 'department_rules');

        DB::transaction(function () use ($client, $orgRules, $departmentRules) {
            SamlOrgRule::where('saml_client_id', $client->id)->delete();
            SamlDepartmentRule::where('saml_client_id', $client->id)->delete();

            foreach ($orgRules as $index => $rule) {
                SamlOrgRule::create([
                    'saml_client_id' => $client->id,
                    'position' => $index + 1,
                    'attribute' => $rule['attribute'],
                    'operator' => $rule['operator'],
                    'value' => $rule['value'],
                    'organization_id' => $rule['organization_id'],
                ]);
            }

            foreach ($departmentRules as $index => $rule) {
                SamlDepartmentRule::create([
                    'saml_client_id' => $client->id,
                    'position' => $index + 1,
                    'attribute' => $rule['attribute'],
                    'operator' => $rule['operator'],
                    'value' => $rule['value'],
                    'department_name' => $rule['department_name'],
                ]);
            }
        });

        return $client->refresh();
    }

    /**
     * The `*` attribute is reserved for the catch-all triple
     * (wildcard/*\/*): any other use of `*` is rejected, and a catch-all may
     * only appear as the last rule in its list — anything after it can never
     * be reached.
     *
     * @param  array<int, array<string, mixed>>  $rules
     */
    private function assertCatchAllPlacement(array $rules, string $field): void
    {
        $catchAllIndex = null;

        foreach ($rules as $index => $rule) {
            $isWildcardAttribute = $rule['attribute'] === '*';
            $isCatchAllTriple = $isWildcardAttribute
                && ($rule['operator'] ?? null) === RoutingOperator::Wildcard->value
                && ($rule['value'] ?? null) === '*';

            if ($isWildcardAttribute && ! $isCatchAllTriple) {
                throw ValidationException::withMessages([
                    "{$field}.{$index}.attribute" => 'The * attribute is reserved for the catch-all rule (wildcard, *).',
                ]);
            }

            if ($catchAllIndex !== null) {
                throw ValidationException::withMessages([
                    "{$field}.{$index}" => 'Rules after a catch-all are unreachable.',
                ]);
            }

            if ($isCatchAllTriple) {
                $catchAllIndex = $index;
            }
        }
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

    /**
     * Routing rules are keyed to the client's current owner (org rules
     * target orgs in its scope, department rules assume its owner's org
     * tree) — re-parenting out from under them would silently leave rules
     * pointing at the wrong hierarchy. Require clearing them first.
     *
     * @param  array<string, mixed>  $validated
     */
    private function assertNoRoutingRulesWhenReparenting(SamlClient $client, array $validated): void
    {
        $reparenting = array_key_exists('owner_type', $validated) || array_key_exists('owner_id', $validated);

        if (! $reparenting) {
            return;
        }

        $ownerChanging = ($validated['owner_type'] ?? $client->owner_type) !== $client->owner_type
            || ($validated['owner_id'] ?? $client->owner_id) !== $client->owner_id;

        if (! $ownerChanging) {
            return;
        }

        if ($client->orgRules()->exists() || $client->departmentRules()->exists()) {
            throw ValidationException::withMessages([
                'owner_id' => 'Clear routing rules before re-parenting this client.',
            ]);
        }
    }
}
