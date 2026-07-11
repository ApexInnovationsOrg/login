<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SsoGrant;
use App\Models\System;
use App\Saml\SamlClientManager;
use App\Support\AdminAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SamlClientController extends Controller
{
    /** Field names the manager's validators accept — the audit trail logs only these. */
    private const EDITABLE_FIELDS = ['name', 'slug', 'owner_type', 'owner_id', 'department_id', 'jit_enabled', 'admin_portal', 'email_domains', 'attribute_map'];

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
        $clients = SamlClient::orderBy('name')->get();

        $orgIds = $clients->where('owner_type', 'organization')->pluck('owner_id')->unique();
        $systemIds = $clients->where('owner_type', 'system')->pluck('owner_id')->unique();

        // Keyed "type:id" — organization and system IDs are independent spaces,
        // so a plain ID-keyed map could collide the two.
        $ownerNames = Organization::whereIn('ID', $orgIds)->pluck('Name', 'ID')
            ->mapWithKeys(fn ($name, $id) => ["organization:{$id}" => $name])
            ->union(
                System::whereIn('ID', $systemIds)->pluck('Name', 'ID')
                    ->mapWithKeys(fn ($name, $id) => ["system:{$id}" => $name])
            )
            ->all();

        return response()->json([
            'data' => $clients
                ->map(fn (SamlClient $client) => $this->item($client, $ownerNames))
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
     * @param  array<string, string>|null  $ownerNames  Preloaded "type:id" => name map (index());
     *                                                  null falls back to a per-client lookup (detail()).
     * @return array<string, mixed>
     */
    private function item(SamlClient $client, ?array $ownerNames = null): array
    {
        $cert = $this->manager->certificateStatus($client);

        $ownerName = $ownerNames !== null
            ? ($ownerNames["{$client->owner_type}:{$client->owner_id}"] ?? null)
            : $client->ownerName();

        return [
            'name' => $client->name,
            'slug' => $client->slug,
            'enabled' => $client->enabled,
            'jit_enabled' => $client->jit_enabled,
            'admin_portal' => $client->admin_portal,
            'owner' => [
                'type' => $client->owner_type,
                'id' => $client->owner_id,
                'name' => $ownerName,
            ],
            'department_id' => $client->department_id,
            'email_domains' => $client->email_domains ?? [],
            'certificate' => [
                'expires_at' => $cert['expires_at']?->toDateString(),
                'expiring' => $cert['expiring'],
            ],
        ];
    }

    /**
     * @param  array<string, string>|null  $ownerNames  See item().
     * @return array<string, mixed>
     */
    private function detail(SamlClient $client, ?array $ownerNames = null): array
    {
        return $this->item($client, $ownerNames) + [
            'acs_url' => $client->acsUrl(),
            'metadata_url' => $client->metadataUrl(),
            'idp_entity_id' => $client->idp_entity_id,
            'idp_sso_url' => $client->idp_sso_url,
            'attribute_map' => $client->attribute_map,
            'grants_count' => SsoGrant::where('owner_type', $client->owner_type)->where('owner_id', $client->owner_id)->count(),
        ];
    }

    private function resolve(string $slug): SamlClient
    {
        $client = SamlClient::where('slug', $slug)->first();

        abort_if($client === null, 404);

        return $client;
    }
}
