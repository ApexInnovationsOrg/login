<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SamlClient;
use App\Models\SsoGrant;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SsoGrantController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        return response()->json(['data' => $this->grantsFor($this->resolve($slug))]);
    }

    /**
     * Replace semantics, mirroring `saml:client update --domains`:
     * the submitted list IS the grant list afterward.
     */
    public function replace(Request $request, string $slug): JsonResponse
    {
        $client = $this->resolve($slug);

        $logins = $request->validate(['logins' => ['present', 'array'], 'logins.*' => ['string']])['logins'];

        $scope = $client->scopedOrganizationIds();

        $users = collect($logins)->map(function (string $login) use ($scope) {
            $user = User::with('department')->where('Login', $login)->first();

            if (! $user) {
                throw ValidationException::withMessages([
                    'logins' => "No user found for {$login}.",
                ]);
            }

            if ($user->department === null || ! in_array((int) $user->department->OrganizationID, $scope, true)) {
                throw ValidationException::withMessages([
                    'logins' => "{$login} does not belong to this client's scope.",
                ]);
            }

            return $user;
        });

        DB::transaction(function () use ($users, $client, $request) {
            SsoGrant::where('owner_type', $client->owner_type)->where('owner_id', $client->owner_id)->delete();

            $users->each(fn (User $user) => SsoGrant::create([
                'user_id' => $user->ID,
                'owner_type' => $client->owner_type,
                'owner_id' => $client->owner_id,
                'granted_by' => (string) $request->header('X-Acting-Admin'),
            ]));
        });

        AdminAudit::log($request, 'replace grants', [
            'slug' => $client->slug,
            'grant_count' => $users->count(),
            'logins' => $users->pluck('Login')->values()->all(),
        ]);

        return response()->json(['data' => $this->grantsFor($client)]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function grantsFor(SamlClient $client): array
    {
        return SsoGrant::with('user')
            ->where('owner_type', $client->owner_type)
            ->where('owner_id', $client->owner_id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (SsoGrant $grant) => [
                'login' => $grant->user?->Login,
                'first_name' => $grant->user?->FirstName,
                'last_name' => $grant->user?->LastName,
                'granted_by' => $grant->granted_by,
                'created_at' => $grant->created_at?->toDateTimeString(),
            ])->values()->all();
    }

    private function resolve(string $slug): SamlClient
    {
        $client = SamlClient::where('slug', $slug)->first();

        abort_if($client === null, 404);

        return $client;
    }
}
