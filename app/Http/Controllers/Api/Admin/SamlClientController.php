<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SsoGrant;
use App\Saml\SamlClientManager;
use App\Support\AdminAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SamlClientController extends Controller
{
    /** Field names the manager's validators accept — the audit trail logs only these. */
    private const EDITABLE_FIELDS = ['name', 'slug', 'organization_id', 'department_id', 'jit_enabled', 'admin_portal', 'email_domains', 'attribute_map'];

    public function __construct(private SamlClientManager $manager) {}

    /**
     * @return array<int, string>
     */
    private function submittedEditableFields(Request $request): array
    {
        return array_values(array_intersect(array_keys($request->all()), self::EDITABLE_FIELDS));
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SamlClient::orderBy('name')->get()
                ->map(fn (SamlClient $client) => $this->item($client))
                ->values(),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        return response()->json(['data' => $this->detail($this->resolve($slug))]);
    }

    public function store(Request $request): JsonResponse
    {
        $client = $this->manager->create($request->all());

        $context = [
            'slug' => $client->slug,
            'fields' => $this->submittedEditableFields($request),
        ];

        if (array_key_exists('email_domains', $request->all())) {
            $context['email_domains'] = $client->email_domains ?? [];
        }

        AdminAudit::log($request, 'create client', $context);

        return response()->json(['data' => $this->detail($client)], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $client = $this->manager->update($this->resolve($slug), $request->all());

        $context = [
            'slug' => $client->slug,
            'fields' => $this->submittedEditableFields($request),
        ];

        if (array_key_exists('email_domains', $request->all())) {
            $context['email_domains'] = $client->email_domains ?? [];
        }

        AdminAudit::log($request, 'update client', $context);

        return response()->json(['data' => $this->detail($client)]);
    }

    public function idpMetadata(Request $request, string $slug): JsonResponse
    {
        $xml = (string) $request->input('xml', '');

        try {
            $client = $this->manager->updateFromIdpMetadata($this->resolve($slug), $xml);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['xml' => $e->getMessage()]);
        }

        // Metadata uploads only touch the fixed idp_* trio (entity_id, sso_url, certificate), so no fields context is logged.
        AdminAudit::log($request, 'idp metadata', ['slug' => $client->slug]);

        return response()->json(['data' => $this->detail($client)]);
    }

    public function enable(Request $request, string $slug): JsonResponse
    {
        return $this->setEnabled($request, $slug, true);
    }

    public function disable(Request $request, string $slug): JsonResponse
    {
        return $this->setEnabled($request, $slug, false);
    }

    private function setEnabled(Request $request, string $slug, bool $enabled): JsonResponse
    {
        $client = $this->manager->setEnabled($this->resolve($slug), $enabled);

        AdminAudit::log($request, $enabled ? 'enable client' : 'disable client', [
            'slug' => $client->slug,
            'enabled' => $enabled,
        ]);

        return response()->json(['data' => $this->detail($client)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function item(SamlClient $client): array
    {
        $cert = $this->manager->certificateStatus($client);

        return [
            'name' => $client->name,
            'slug' => $client->slug,
            'enabled' => $client->enabled,
            'jit_enabled' => $client->jit_enabled,
            'admin_portal' => $client->admin_portal,
            'organization_id' => $client->organization_id,
            'department_id' => $client->department_id,
            'email_domains' => $client->email_domains ?? [],
            'certificate' => [
                'expires_at' => $cert['expires_at']?->toDateString(),
                'expiring' => $cert['expiring'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(SamlClient $client): array
    {
        return $this->item($client) + [
            'acs_url' => $client->acsUrl(),
            'metadata_url' => $client->metadataUrl(),
            'idp_entity_id' => $client->idp_entity_id,
            'idp_sso_url' => $client->idp_sso_url,
            'attribute_map' => $client->attribute_map,
            'organization_name' => Organization::where('ID', $client->organization_id)->value('Name'),
            'grants_count' => SsoGrant::where('organization_id', $client->organization_id)->count(),
        ];
    }

    private function resolve(string $slug): SamlClient
    {
        $client = SamlClient::where('slug', $slug)->first();

        abort_if($client === null, 404);

        return $client;
    }
}
