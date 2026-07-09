<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use App\Saml\AdminSsoHandoff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Support\SamlResponseFactory;
use Tests\TestCase;

class AdminPortalSamlLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
            'saml.replay_store' => 'array',
            'saml.admin_portal_url' => 'http://localhost/admin',
        ]);

        SamlClient::factory()->adminPortal()->create([
            'slug' => 'apex-admin',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
            'organization_id' => 933,
            'jit_enabled' => false,
        ]);

        DB::table('Employees')->insert([
            'Email' => 'sso.user@acme.test', 'FirstName' => 'Sso', 'LastName' => 'User',
            'Password' => md5('p6^8&irrelevant'), 'Active' => 'Y',
            'PasswordLastChanged' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function acs(array $responseOverrides = []): TestResponse
    {
        return $this->post('/saml/apex-admin/acs', [
            'SAMLResponse' => SamlResponseFactory::make(
                ['destination' => url('/saml/apex-admin/acs')] + $responseOverrides
            ),
        ]);
    }

    public function test_active_employee_is_redirected_to_portal_with_token(): void
    {
        $response = $this->acs();

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('http://localhost/admin/ssoLogon.php?token=', $location);

        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $payload = app(AdminSsoHandoff::class)->redeem($query['token']);
        $this->assertSame('Sso User', $payload['name']);
    }

    public function test_no_laravel_session_and_no_user_row(): void
    {
        $this->acs();

        $this->assertGuest();
        $this->assertDatabaseMissing('Users', ['Login' => 'sso.user@acme.test']);
    }

    public function test_unknown_email_gets_rejection_page(): void
    {
        $response = $this->acs(['nameId' => 'ghost@acme.test', 'attributes' => [
            'email' => 'ghost@acme.test', 'firstName' => 'Gho', 'lastName' => 'St',
        ]]);

        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function test_inactive_employee_gets_rejection_page(): void
    {
        DB::table('Employees')->where('Email', 'sso.user@acme.test')->update(['Active' => 'N']);

        $this->acs()->assertStatus(403);
    }
}
