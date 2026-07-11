<?php

namespace App\Saml;

use App\Models\SamlClient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SamlUserProvisioner
{
    /**
     * @param  array{organization_id: int, department_id: ?int}|null  $placement
     */
    public function provision(SamlClient $client, string $email, ?string $firstName, ?string $lastName, ?array $placement = null): User
    {
        $user = User::where('Login', $email)->first();

        if ($user && $user->Disabled === 'Y') {
            throw new SamlLoginRejected(
                'Your account has been disabled. Please contact your administrator.',
                ['reason' => 'disabled_user', 'login' => $email],
            );
        }

        if ($user) {
            $user = $this->syncName($user, $firstName, $lastName);

            $routedDepartment = $placement['department_id'] ?? null;

            if ($routedDepartment !== null && $routedDepartment !== (int) $user->DepartmentID) {
                $from = $user->DepartmentID;
                $user->DepartmentID = $routedDepartment;
                $user->save();

                Log::info('SAML routed user to department', [
                    'client' => $client->slug, 'user_id' => $user->ID, 'from' => $from, 'to' => $routedDepartment,
                ]);
            }

            return $user;
        }

        if (! $client->jit_enabled) {
            throw new SamlLoginRejected(
                'No account was found for your email address. Please contact your administrator.',
                ['reason' => 'unknown_user_jit_disabled', 'login' => $email],
            );
        }

        if ($placement === null && ! $client->ownedByOrganization()) {
            // A system-owned client has no home org to aim the finish-account
            // flow at, and no routing rule (incl. catch-all) claimed this
            // login — reject fail-closed rather than guess a placement.
            throw new SamlLoginRejected(
                'Your account could not be placed automatically. Please contact your administrator.',
                ['reason' => 'unrouted_user', 'login' => $email],
            );
        }

        $user = User::factory()->newModel()->forceFill([
            'Login' => $email,
            // Placeholder when the IdP omitted a name; SamlController logs that misconfiguration.
            'FirstName' => $firstName ?? 'FirstName',
            'LastName' => $lastName ?? 'LastName',
            // Legacy schema: DepartmentID is NOT NULL; 0 routes through finishAccountCreation
            // A resolved department rule wins; otherwise org-owned clients
            // keep their static default, and system-owned placements land in
            // the finish flow of the placed org (DepartmentID 0).
            'DepartmentID' => $placement['department_id']
                ?? ($client->ownedByOrganization() ? ($client->department_id ?? 0) : 0),
            'CredentialID' => 0,
            'Password' => Hash::make(Str::random(40)),
            'CreationDate' => now()->format('Y-m-d H:i:s'),
            'Active' => 'Y',
            'Disabled' => 'N',
            // Production column default is 'Y', which would trap SSO users in the forced-reset flow
            'PasswordChangedByAdmin' => 'N',
            'LMS' => 'N',
            'Locale' => 'en-us',
            'SecurityAnswer' => '',
        ]);

        $user->save();

        return $user;
    }

    /**
     * Reflect name changes from the IdP onto an existing user. Only non-null
     * values are applied, so a misconfigured or partial assertion never wipes
     * a real stored name with the SamlController placeholder fallback.
     */
    private function syncName(User $user, ?string $firstName, ?string $lastName): User
    {
        if ($firstName !== null && $firstName !== $user->FirstName) {
            $user->FirstName = $firstName;
        }

        if ($lastName !== null && $lastName !== $user->LastName) {
            $user->LastName = $lastName;
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return $user;
    }
}
