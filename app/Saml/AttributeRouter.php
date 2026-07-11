<?php

namespace App\Saml;

use App\Models\Department;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;

/**
 * Two-stage placement from a SAML assertion's attributes (spec:
 * docs/specs/2026-07-10-attribute-routing.md). Stage 1 answers "which
 * organization": the owner for org-owned clients, the ordered org rules for
 * system-owned ones. Stage 2 answers "which department" by NAME within the
 * resolved org — first rule that matches AND resolves wins, so shared rule
 * sets survive orgs that lack a given department.
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

        foreach ($client->orgRules as $rule) {
            if ($this->ruleMatches($rule, $attributes)) {
                return $rule->organization_id;
            }
        }

        return null;
    }

    private function resolveDepartment(SamlClient $client, array $attributes, int $organizationId): ?int
    {
        // One query: the resolved org's active departments, name → ID,
        // lowercased for the case-insensitive rule-name resolution.
        $departments = Department::where('OrganizationID', $organizationId)
            ->where('Active', 'Y')
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
