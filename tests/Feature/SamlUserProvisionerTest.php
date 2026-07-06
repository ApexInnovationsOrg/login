<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use App\Models\User;
use App\Saml\SamlLoginRejected;
use App\Saml\SamlUserProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlUserProvisionerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function provision(SamlClient $client, string $email = 'sso.user@acme.test'): User
    {
        return app(SamlUserProvisioner::class)->provision($client, $email, 'Sso', 'User');
    }

    public function test_matches_existing_user_by_login(): void
    {
        $existing = User::factory()->create(['Login' => 'known@acme.test']);
        $client = SamlClient::factory()->create(['jit_enabled' => false]);

        $user = $this->provision($client, 'known@acme.test');

        $this->assertSame($existing->ID, $user->ID);
    }

    public function test_rejects_unknown_user_when_jit_disabled(): void
    {
        $client = SamlClient::factory()->create(['jit_enabled' => false]);

        $this->expectException(SamlLoginRejected::class);

        $this->provision($client);
    }

    public function test_rejects_disabled_user(): void
    {
        User::factory()->disabled()->create(['Login' => 'off@acme.test']);
        $client = SamlClient::factory()->create();

        $this->expectException(SamlLoginRejected::class);

        $this->provision($client, 'off@acme.test');
    }

    public function test_jit_creates_user_with_safe_legacy_defaults(): void
    {
        $client = SamlClient::factory()->create(['jit_enabled' => true, 'department_id' => null]);

        $user = $this->provision($client);

        $this->assertSame('sso.user@acme.test', $user->Login);
        $this->assertSame('Sso', $user->FirstName);
        $this->assertSame(0, (int) $user->DepartmentID);   // finish-account flow
        $this->assertSame(0, (int) $user->CredentialID);
        $this->assertSame('N', $user->Disabled);
        $this->assertSame('N', $user->PasswordChangedByAdmin); // prod default is Y — must be overridden
        $this->assertNotNull($user->CreationDate);
        $this->assertNotSame('', $user->Password);
    }

    public function test_jit_uses_client_default_department(): void
    {
        $client = SamlClient::factory()->create(['jit_enabled' => true, 'department_id' => 1]);

        $user = $this->provision($client);

        $this->assertSame(1, (int) $user->DepartmentID);
    }
}
