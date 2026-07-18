<?php

namespace Tests\Support;

trait InteractsWithAdminApi
{
    /**
     * Configure the admin API token that adminApiHeaders() authenticates
     * against. Call from setUp() after parent::setUp().
     */
    private function configureAdminApi(): void
    {
        config(['admin.api_token' => 'test-token']);
    }

    /**
     * @return array<string, string>
     */
    private function adminApiHeaders(?string $actingAdmin = '1:Test Admin'): array
    {
        $headers = ['Authorization' => 'Bearer test-token'];

        if ($actingAdmin !== null) {
            $headers['X-Acting-Admin'] = $actingAdmin;
        }

        return $headers;
    }
}
