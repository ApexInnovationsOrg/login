<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only pickers for the admin UI. Lookups, not writes — no manager
 * involvement, no audit (GETs are unaudited by design).
 */
class LookupController extends Controller
{
    public function organizations(Request $request): JsonResponse
    {
        $q = (string) $request->query('q', '');

        return response()->json([
            'data' => Organization::query()
                ->when($q !== '', fn ($query) => $query->where('Name', 'like', '%'.$q.'%'))
                ->orderBy('Name')
                ->limit(25)
                ->get()
                ->map(fn (Organization $org) => ['id' => $org->ID, 'name' => $org->Name])
                ->values(),
        ]);
    }

    public function departments(int $organizationId): JsonResponse
    {
        return response()->json([
            'data' => Department::query()
                ->where('OrganizationID', $organizationId)
                ->where('Active', 'Y')
                ->orderBy('Name')
                ->get()
                ->map(fn (Department $dept) => ['id' => $dept->ID, 'name' => $dept->Name])
                ->values(),
        ]);
    }

    public function users(Request $request, string $slug): JsonResponse
    {
        $client = SamlClient::where('slug', $slug)->first();

        abort_if($client === null, 404);

        $q = (string) $request->query('q', '');

        $departmentIds = Department::where('OrganizationID', $client->organization_id)->pluck('ID');

        return response()->json([
            'data' => User::query()
                ->whereIn('DepartmentID', $departmentIds)
                ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                    $inner->where('Login', 'like', '%'.$q.'%')
                        ->orWhere('FirstName', 'like', '%'.$q.'%')
                        ->orWhere('LastName', 'like', '%'.$q.'%');
                }))
                ->with('department')
                ->orderBy('LastName')
                ->limit(25)
                ->get()
                ->map(fn (User $user) => [
                    'login' => $user->Login,
                    'first_name' => $user->FirstName,
                    'last_name' => $user->LastName,
                    'department' => $user->department?->Name,
                ])
                ->values(),
        ]);
    }
}
