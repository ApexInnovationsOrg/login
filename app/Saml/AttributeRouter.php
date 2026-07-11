<?php

namespace App\Saml;

use App\Models\Department;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use Illuminate\Support\Facades\Log;

/**
 * Two-stage placement from a SAML assertion's attributes (spec:
 * docs/specs/2026-07-10-attribute-routing.md). Stage 1 answers "which
 * organization": the owner for org-owned clients, the ordered org rules for
 * system-owned ones. Stage 2 answers "which department" by NAME within the
 * resolved org — first rule that matches AND resolves wins, so shared rule
 * sets survive orgs that lack a given department.
 *
 * Otherwise a pure function over its inputs — one `Departments` query per
 * login on the resolved org, plus the scope query for system-owned clients —
 * logs only the stale-scope warning (no user lookups, no session).
 */
class AttributeRouter
{
    /**
     * @param  array<string, array<int, mixed>>  $attributes  php-saml getAttributes()
     * @return array{organization_id: int, department_id: ?int}|null
     */
    public function route(SamlClient $client, array $attributes, ?int $fallbackOrganizationId = null): ?array
    {
        $organizationId = $this->resolveOrganization($client, $attributes) ?? $fallbackOrganizationId;

        if ($organizationId === null) {
            return null;
        }

        return [
            'organization_id' => $organizationId,
            'department_id' => $this->resolveDepartment($client, $attributes, $organizationId),
        ];
    }

    private function resolveOrganization(SamlClient $client, array $attributes): ?int
    {
        if ($client->ownedByOrganization()) {
            return $client->owner_id;
        }

        // Defense-in-depth against stale rules left behind by a re-parent
        // that predates the manager-level guard (or a direct DB edit): a
        // rule targeting an org outside the client's current scope is
        // skipped rather than trusted, and the skip is logged so it gets
        // noticed and cleaned up.
        $scope = $client->scopedOrganizationIds();

        foreach ($client->orgRules as $rule) {
            if (! $this->ruleMatches($rule, $attributes)) {
                continue;
            }

            if (! in_array($rule->organization_id, $scope, true)) {
                Log::warning('Routing rule targets organization outside client scope', [
                    'client' => $client->slug,
                    'organization_id' => $rule->organization_id,
                ]);

                continue;
            }

            return $rule->organization_id;
        }

        return null;
    }

    private function resolveDepartment(SamlClient $client, array $attributes, int $organizationId): ?int
    {
        if ($client->departmentRules->isEmpty()) {
            return null;
        }

        // One query: the resolved org's active departments, name → ID,
        // lowercased for the case-insensitive rule-name resolution.
        $departments = Department::where('OrganizationID', $organizationId)
            ->where('Active', 'Y')
            // duplicate names in an org resolve to the lowest ID, deterministically
            // (descending order + pluck-overwrite semantics: the last-applied,
            // lowest-ID row wins the keyed collection)
            ->orderByDesc('ID')
            ->pluck('ID', 'Name')
            ->mapWithKeys(fn ($id, $name) => [mb_strtolower($name) => (int) $id]);

        foreach ($client->departmentRules as $rule) {
            $resolved = $departments->get(mb_strtolower($rule->department_name));

            // Fall through when the name doesn't exist here: shared rule
            // sets stay usable across orgs missing a department.
            if ($resolved !== null && $this->ruleMatches($rule, $attributes)) {
                return $resolved;
            }
        }

        return null;
    }

    private function ruleMatches(SamlOrgRule|SamlDepartmentRule $rule, array $attributes): bool
    {
        if ($rule->isCatchAll()) {
            return true;
        }

        return $rule->operator->matchesAny($attributes[$rule->attribute] ?? [], $rule->value);
    }
}
