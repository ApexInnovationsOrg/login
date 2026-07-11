<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Saml\SamlClientManager;
use App\Support\AdminAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoutingRuleController extends Controller
{
    public function __construct(private SamlClientManager $manager) {}

    public function show(string $slug): JsonResponse
    {
        return response()->json(['data' => $this->rules($this->resolve($slug))]);
    }

    public function replace(Request $request, string $slug): JsonResponse
    {
        $client = $this->resolve($slug);

        $orgRules = $request->input('org_rules', []);
        $departmentRules = $request->input('department_rules', []);

        $client = $this->manager->replaceRoutingRules($client, $orgRules, $departmentRules);

        AdminAudit::log($request, 'replace routing rules', [
            'slug' => $client->slug,
            'org_rule_count' => count($orgRules),
            'department_rule_count' => count($departmentRules),
            'org_rules' => $orgRules,
            'department_rules' => $departmentRules,
        ]);

        return response()->json(['data' => $this->rules($client)]);
    }

    public function routableOrganizations(string $slug): JsonResponse
    {
        $client = $this->resolve($slug);

        return response()->json([
            'data' => Organization::whereIn('ID', $client->scopedOrganizationIds())
                ->orderBy('Name')
                ->get()
                ->map(fn (Organization $org) => ['id' => $org->ID, 'name' => $org->Name])
                ->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(SamlClient $client): array
    {
        $orgNames = Organization::whereIn('ID', $client->orgRules->pluck('organization_id')->unique())
            ->pluck('Name', 'ID');

        return [
            'org_rules' => $client->orgRules->map(fn (SamlOrgRule $rule) => [
                'attribute' => $rule->attribute,
                'operator' => $rule->operator->value,
                'value' => $rule->value,
                'organization_id' => $rule->organization_id,
                'organization_name' => $orgNames->get($rule->organization_id),
                'catch_all' => $rule->isCatchAll(),
            ])->values(),
            'department_rules' => $client->departmentRules->map(fn (SamlDepartmentRule $rule) => [
                'attribute' => $rule->attribute,
                'operator' => $rule->operator->value,
                'value' => $rule->value,
                'department_name' => $rule->department_name,
                'catch_all' => $rule->isCatchAll(),
            ])->values(),
        ];
    }

    private function resolve(string $slug): SamlClient
    {
        $client = SamlClient::where('slug', $slug)->first();

        abort_if($client === null, 404);

        return $client;
    }
}
