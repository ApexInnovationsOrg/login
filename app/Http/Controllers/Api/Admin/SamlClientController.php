<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SamlClient;
use App\Models\SsoGrant;
use App\Saml\SamlClientManager;
use Illuminate\Http\JsonResponse;

class SamlClientController extends Controller
{
    public function __construct(private SamlClientManager $manager) {}

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
