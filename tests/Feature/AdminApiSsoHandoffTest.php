<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use App\Saml\AdminSsoHandoff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminApiSsoHandoffTest extends TestCase
{
    use RefreshDatabase;

    // Alphabetically the first RefreshDatabase class in the suite, so it
    // decides whether the one-time migrate:fresh seeds. $seed = true keeps
    // the suite-wide seed pass alive (see ReferenceDataSeederTest's note).
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        config(['admin.api_token' => 'test-token', 'saml.replay_store' => 'array',
            'saml.admin_portal_url' => 'http://localhost/admin']);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer test-token', 'X-Acting-Admin' => '1:Test Admin'];
    }

    private function mintToken(): string
    {
        DB::table('Employees')->insert([
            'Email' => 'jane@apexinnovations.com', 'FirstName' => 'Jane', 'LastName' => 'Doe',
            'Password' => md5('p6^8&x'), 'Active' => 'Y',
            'PasswordLastChanged' => now()->format('Y-m-d H:i:s'),
        ]);

        $client = SamlClient::factory()->adminPortal()->create();
        $url = app(AdminSsoHandoff::class)->initiate($client, 'jane@apexinnovations.com');
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return $query['token'];
    }

    public function test_token_redeems_exactly_once(): void
    {
        $token = $this->mintToken();

        $this->postJson('/api/admin/sso-handoff/redeem', ['token' => $token], $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.name', 'Jane Doe');

        $this->postJson('/api/admin/sso-handoff/redeem', ['token' => $token], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_unknown_token_404s(): void
    {
        $this->postJson('/api/admin/sso-handoff/redeem', ['token' => 'nope'], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_requires_admin_token(): void
    {
        $this->postJson('/api/admin/sso-handoff/redeem', ['token' => 'anything'])
            ->assertUnauthorized();
    }
}
