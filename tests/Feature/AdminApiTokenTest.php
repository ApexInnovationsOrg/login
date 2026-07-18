<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminApiTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['api', 'admin.api'])->prefix('api/admin-probe')->group(function () {
            Route::get('/ping', fn () => response()->json(['pong' => true]));
            Route::post('/ping', fn () => response()->json(['pong' => true]));
        });
    }

    public function test_unconfigured_token_returns_503(): void
    {
        config(['admin.api_token' => null]);

        $this->getJson('/api/admin-probe/ping', ['Authorization' => 'Bearer anything'])
            ->assertStatus(503);
    }

    public function test_missing_or_wrong_token_returns_401(): void
    {
        config(['admin.api_token' => 'test-token']);

        $this->getJson('/api/admin-probe/ping')->assertStatus(401);
        $this->getJson('/api/admin-probe/ping', ['Authorization' => 'Bearer nope'])->assertStatus(401);
    }

    public function test_mutation_without_acting_admin_returns_400(): void
    {
        config(['admin.api_token' => 'test-token']);

        $this->postJson('/api/admin-probe/ping', [], ['Authorization' => 'Bearer test-token'])
            ->assertStatus(400);
    }

    public function test_valid_requests_pass(): void
    {
        config(['admin.api_token' => 'test-token']);

        $this->getJson('/api/admin-probe/ping', ['Authorization' => 'Bearer test-token'])
            ->assertOk()->assertJson(['pong' => true]);

        $this->postJson('/api/admin-probe/ping', [], [
            'Authorization' => 'Bearer test-token',
            'X-Acting-Admin' => '1:Test Admin',
        ])->assertOk();
    }
}
