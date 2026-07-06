<?php

namespace App\Saml;

use App\Models\SamlClient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SamlUserProvisioner
{
    public function provision(SamlClient $client, string $email, ?string $firstName, ?string $lastName): User
    {
        $user = User::where('Login', $email)->first();

        if ($user && $user->Disabled === 'Y') {
            throw new SamlLoginRejected(
                'Your account has been disabled. Please contact your administrator.',
                ['reason' => 'disabled_user', 'login' => $email],
            );
        }

        if ($user) {
            return $this->syncName($user, $firstName, $lastName);
        }

        if (! $client->jit_enabled) {
            throw new SamlLoginRejected(
                'No account was found for your email address. Please contact your administrator.',
                ['reason' => 'unknown_user_jit_disabled', 'login' => $email],
            );
        }

        $user = User::factory()->newModel()->forceFill([
            'Login' => $email,
            // Placeholder when the IdP omitted a name; SamlController logs that misconfiguration.
            'FirstName' => $firstName ?? 'FirstName',
            'LastName' => $lastName ?? 'LastName',
            // Legacy schema: DepartmentID is NOT NULL; 0 routes through finishAccountCreation
            'DepartmentID' => $client->department_id ?? 0,
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
