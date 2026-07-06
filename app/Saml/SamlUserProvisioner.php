<?php

namespace App\Saml;

use App\Models\SamlClient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SamlUserProvisioner
{
    public function provision(SamlClient $client, string $email, string $firstName, string $lastName): User
    {
        $user = User::where('Login', $email)->first();

        if ($user && $user->Disabled === 'Y') {
            throw new SamlLoginRejected(
                'Your account has been disabled. Please contact your administrator.',
                ['reason' => 'disabled_user', 'login' => $email],
            );
        }

        if ($user) {
            return $user;
        }

        if (! $client->jit_enabled) {
            throw new SamlLoginRejected(
                'No account was found for your email address. Please contact your administrator.',
                ['reason' => 'unknown_user_jit_disabled', 'login' => $email],
            );
        }

        $user = User::factory()->newModel()->forceFill([
            'Login' => $email,
            'FirstName' => $firstName,
            'LastName' => $lastName,
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
}
