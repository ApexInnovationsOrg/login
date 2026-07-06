<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SsoLookupTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // The throttle middleware counts hits in the cache under its own
        // hashed key; flush so hits from earlier tests never bleed into
        // the throttle assertion.
        Cache::flush();
    }

    public function test_matching_domain_returns_the_sp_login_url(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'email_domains' => ['acme.com']]);

        $this->postJson('/sso/lookup', ['email' => 'jane@ACME.com'])
            ->assertOk()
            ->assertExactJson(['sso' => '/saml/acme/login']);
    }

    public function test_unknown_domain_and_disabled_client_and_garbage_are_identical(): void
    {
        SamlClient::factory()->create(['enabled' => false, 'email_domains' => ['disabled.com']]);

        foreach (['jane@nowhere.com', 'jane@disabled.com', 'not-an-email', '', null] as $email) {
            $this->postJson('/sso/lookup', ['email' => $email])
                ->assertOk()
                ->assertExactJson(['sso' => null]);
        }
    }

    public function test_lookup_is_throttled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/sso/lookup', ['email' => 'jane@nowhere.com'])->assertOk();
        }

        $this->postJson('/sso/lookup', ['email' => 'jane@nowhere.com'])->assertStatus(429);
    }
}
