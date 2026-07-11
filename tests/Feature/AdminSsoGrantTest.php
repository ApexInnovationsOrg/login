<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SamlClient;
use App\Models\SsoGrant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSsoGrantTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private SamlClient $client;

    private Department $department;

    private function headers(): array
    {
        return ['Authorization' => 'Bearer test-token', 'X-Acting-Admin' => '7:Jane Admin'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin.api_token' => 'test-token']);

        $this->department = Department::factory()->create();
        $this->client = SamlClient::factory()->create([
            'slug' => 'acme',
            'owner_id' => $this->department->OrganizationID,
        ]);
    }

    public function test_replace_and_list_grants(): void
    {
        $user = User::factory()->create([
            'Login' => 'grantee@acme.com',
            'DepartmentID' => $this->department->ID,
        ]);

        $this->putJson('/api/admin/saml-clients/acme/grants', ['logins' => ['grantee@acme.com']],
            $this->headers())
            ->assertOk()
            ->assertJsonPath('data.0.login', 'grantee@acme.com')
            ->assertJsonPath('data.0.granted_by', '7:Jane Admin');

        $this->getJson('/api/admin/saml-clients/acme/grants', $this->headers())
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('sso_grants', [
            'user_id' => $user->ID,
            'owner_id' => $this->client->owner_id,
        ]);
    }

    public function test_replace_removes_grants_not_in_the_list(): void
    {
        $keep = User::factory()->create(['Login' => 'keep@acme.com', 'DepartmentID' => $this->department->ID]);
        $drop = User::factory()->create(['Login' => 'drop@acme.com', 'DepartmentID' => $this->department->ID]);
        SsoGrant::factory()->create(['user_id' => $drop->ID, 'owner_id' => $this->client->owner_id]);

        $this->putJson('/api/admin/saml-clients/acme/grants', ['logins' => ['keep@acme.com']],
            $this->headers())->assertOk()->assertJsonCount(1, 'data');

        $this->assertDatabaseMissing('sso_grants', ['user_id' => $drop->ID]);
    }

    public function test_unknown_login_and_wrong_org_are_422(): void
    {
        $otherDept = Department::factory()->create();
        User::factory()->create(['Login' => 'other@org.com', 'DepartmentID' => $otherDept->ID]);

        $this->putJson('/api/admin/saml-clients/acme/grants', ['logins' => ['ghost@acme.com']],
            $this->headers())->assertStatus(422)->assertJsonValidationErrors('logins');

        $this->putJson('/api/admin/saml-clients/acme/grants', ['logins' => ['other@org.com']],
            $this->headers())->assertStatus(422)->assertJsonValidationErrors('logins');
    }

    public function test_empty_logins_list_clears_all_grants(): void
    {
        $user = User::factory()->create(['Login' => 'grantee@acme.com', 'DepartmentID' => $this->department->ID]);
        SsoGrant::factory()->create(['user_id' => $user->ID, 'owner_id' => $this->client->owner_id]);

        $this->putJson('/api/admin/saml-clients/acme/grants', ['logins' => []], $this->headers())
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->assertDatabaseCount('sso_grants', 0);
    }

    public function test_user_with_null_department_is_rejected(): void
    {
        User::factory()->create(['Login' => 'nodept@acme.com', 'DepartmentID' => 0]);

        $this->putJson('/api/admin/saml-clients/acme/grants', ['logins' => ['nodept@acme.com']],
            $this->headers())->assertStatus(422)->assertJsonValidationErrors('logins');
    }
}
