<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Support\SamlResponseFactory;
use Tests\TestCase;

class SamlLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private SamlClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'saml.sp.cert_path' => base_path('tests/Fixtures/saml/sp.crt'),
            'saml.sp.key_path' => base_path('tests/Fixtures/saml/sp.key'),
            'saml.replay_store' => 'array',
        ]);

        $this->client = SamlClient::factory()->create([
            'slug' => 'acme',
            'idp_entity_id' => 'https://idp.acme.test/metadata',
            'idp_sso_url' => 'https://idp.acme.test/sso',
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/idp.crt')),
            'organization_id' => 933,
            'department_id' => null,
            'jit_enabled' => true,
        ]);
    }

    private function acs(array $responseOverrides = []): TestResponse
    {
        return $this->post('/saml/acme/acs', [
            'SAMLResponse' => SamlResponseFactory::make($responseOverrides),
        ]);
    }

    public function test_jit_login_creates_user_and_redirects_to_finish_flow(): void
    {
        $response = $this->acs();

        $this->assertAuthenticated();
        $response->assertRedirect('/finishAccountCreation'); // DepartmentID 0
        $this->assertDatabaseHas('Users', ['Login' => 'sso.user@acme.test']);
    }

    public function test_login_sets_legacy_session_contract(): void
    {
        $response = $this->acs();

        $user = User::where('Login', 'sso.user@acme.test')->first();
        $response->assertSessionHas('userId', $user->ID);
        $response->assertSessionHas('userID', $user->ID);
        $response->assertSessionHas('userName', 'Sso User');
        $response->assertSessionHas('Username', 'Sso User');
        $response->assertSessionHas('Organization', 933);
        $response->assertSessionHas('SAML', true);
        $this->assertNotNull($user->LastLoginDate);
    }

    public function test_finished_user_gets_plain_302_to_main_site(): void
    {
        User::factory()->create(['Login' => 'done@acme.test']); // dept 1, cred 1

        $response = $this->acs(['nameId' => 'done@acme.test', 'attributes' => [
            'email' => 'done@acme.test', 'firstName' => 'Done', 'lastName' => 'User',
        ]]);

        $response->assertStatus(302);
        $response->assertRedirect('https://www.apexinnovations.com/MyCurriculum.php');
    }

    public function test_replayed_assertion_is_rejected(): void
    {
        $fixed = ['assertionId' => '_replay-me-once'];

        $this->acs($fixed)->assertRedirect('/finishAccountCreation');
        auth()->logout();

        $this->acs($fixed)->assertStatus(403);
        $this->assertGuest();
    }

    public function test_bad_signature_is_rejected(): void
    {
        // Signed by the SP fixture key, which is not the configured IdP certificate
        $response = $this->acs([
            'signedKeyPath' => base_path('tests/Fixtures/saml/sp.key'),
            'signedCertPath' => base_path('tests/Fixtures/saml/sp.crt'),
        ]);

        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function test_expired_assertion_is_rejected(): void
    {
        $response = $this->acs(['notOnOrAfter' => now()->subMinutes(10)]);

        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function test_unknown_client_404s(): void
    {
        $this->post('/saml/nope/acs', ['SAMLResponse' => SamlResponseFactory::make()])
            ->assertNotFound();
    }

    public function test_disabled_client_is_rejected(): void
    {
        $this->client->update(['enabled' => false]);

        $this->acs()->assertNotFound();
        $this->assertGuest();
    }

    public function test_jit_disabled_rejects_unknown_user(): void
    {
        $this->client->update(['jit_enabled' => false]);

        $response = $this->acs();

        $response->assertStatus(403);
        $response->assertSee('contact your administrator');
        $this->assertGuest();
    }

    public function test_entra_style_attribute_map_works(): void
    {
        $claims = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims';
        $this->client->update(['attribute_map' => [
            'email' => "$claims/emailaddress",
            'first_name' => "$claims/givenname",
            'last_name' => "$claims/surname",
        ]]);

        $response = $this->acs(['attributes' => [
            "$claims/emailaddress" => 'entra.user@acme.test',
            "$claims/givenname" => 'Entra',
            "$claims/surname" => 'User',
        ], 'nameId' => 'entra.user@acme.test']);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('Users', ['Login' => 'entra.user@acme.test', 'FirstName' => 'Entra']);
    }

    public function test_missing_email_attribute_falls_back_to_nameid(): void
    {
        $response = $this->acs(['attributes' => [
            'firstName' => 'Only', 'lastName' => 'NameId',
        ], 'nameId' => 'nameid.only@acme.test']);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('Users', ['Login' => 'nameid.only@acme.test']);
    }
}
