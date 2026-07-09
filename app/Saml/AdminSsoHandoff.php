<?php

namespace App\Saml;

use App\Models\Employee;
use App\Models\SamlClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bridges a validated admin-portal SAML assertion into the legacy portal's
 * own session world: match an active Employee (fail closed, never JIT),
 * mint a short-lived single-use token, and send the browser to the portal's
 * ssoLogon.php, which redeems the token server-side through the admin API.
 */
class AdminSsoHandoff
{
    private const KEY_PREFIX = 'admin_sso:token:';

    public function initiate(SamlClient $client, string $email): string
    {
        $employee = Employee::where('Email', $email)->where('Active', 'Y')->first();

        if (! $employee) {
            throw new SamlLoginRejected(
                'Your account is not authorized for the admin portal. Contact your administrator.',
                ['reason' => 'no_employee_match', 'email' => $email],
            );
        }

        $token = Str::random(64);

        Cache::store(config('saml.replay_store'))->put(
            self::KEY_PREFIX.$token,
            ['employee_id' => $employee->ID, 'name' => $employee->FirstName.' '.$employee->LastName],
            (int) config('saml.admin_handoff_ttl'),
        );

        Log::info('SAML admin portal login', [
            'client' => $client->slug,
            'employee_id' => $employee->ID,
        ]);

        return rtrim(config('saml.admin_portal_url'), '/').'/ssoLogon.php?token='.$token;
    }

    /**
     * Single-use: pull deletes atomically, so a second redemption is null.
     */
    public function redeem(string $token): ?array
    {
        return Cache::store(config('saml.replay_store'))->pull(self::KEY_PREFIX.$token);
    }
}
