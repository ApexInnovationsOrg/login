<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\System;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLookupTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function headers(): array
    {
        return ['Authorization' => 'Bearer test-token'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin.api_token' => 'test-token']);
    }

    public function test_organization_search_filters_by_name(): void
    {
        Organization::factory()->create(['Name' => 'Memorial Hermann']);
        Organization::factory()->create(['Name' => 'Acme Hospital']);

        $this->getJson('/api/admin/organizations?q=memorial', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Memorial Hermann')
            ->assertJsonMissing(['name' => 'Acme Hospital']);
    }

    public function test_departments_scoped_to_organization_and_active_only(): void
    {
        $org = Organization::factory()->create();
        Department::factory()->create(['OrganizationID' => $org->ID, 'Name' => 'Emergency', 'Active' => 'Y']);
        Department::factory()->create(['OrganizationID' => $org->ID, 'Name' => 'Closed Wing', 'Active' => 'N']);
        Department::factory()->create(['Name' => 'Other Org Dept', 'Active' => 'Y']);

        $response = $this->getJson("/api/admin/organizations/{$org->ID}/departments", $this->headers())
            ->assertOk();

        $names = array_column($response->json('data'), 'name');
        $this->assertContains('Emergency', $names);
        $this->assertNotContains('Closed Wing', $names);
        $this->assertNotContains('Other Org Dept', $names);
    }

    public function test_user_search_scoped_to_client_org_matches_login_and_name(): void
    {
        $dept = Department::factory()->create();
        $client = SamlClient::factory()->create(['slug' => 'acme', 'owner_id' => $dept->OrganizationID]);
        User::factory()->create(['Login' => 'jane@acme.com', 'FirstName' => 'Jane', 'LastName' => 'Smith', 'DepartmentID' => $dept->ID]);
        $otherDept = Department::factory()->create();
        User::factory()->create(['Login' => 'jane@other.com', 'FirstName' => 'Jane', 'LastName' => 'Elsewhere', 'DepartmentID' => $otherDept->ID]);

        $byLogin = $this->getJson('/api/admin/saml-clients/acme/users?q=jane@acme', $this->headers())->assertOk();
        $this->assertSame('jane@acme.com', $byLogin->json('data.0.login'));
        $this->assertCount(1, $byLogin->json('data'));

        $byName = $this->getJson('/api/admin/saml-clients/acme/users?q=Smith', $this->headers())->assertOk();
        $this->assertSame('jane@acme.com', $byName->json('data.0.login'));
        $this->assertCount(1, $byName->json('data'));
    }

    public function test_user_search_unknown_slug_404s(): void
    {
        $this->getJson('/api/admin/saml-clients/nope/users?q=x', $this->headers())->assertNotFound();
    }

    public function test_departments_unknown_organization_404s(): void
    {
        $this->getJson('/api/admin/organizations/999999/departments', $this->headers())->assertNotFound();
    }

    public function test_departments_non_numeric_organization_404s(): void
    {
        $this->getJson('/api/admin/organizations/not-a-number/departments', $this->headers())->assertNotFound();
    }

    public function test_organization_search_is_limited_to_25_results(): void
    {
        for ($i = 0; $i < 30; $i++) {
            Organization::factory()->create(['Name' => 'Prefix Org '.Str::random(6)]);
        }

        $response = $this->getJson('/api/admin/organizations?q=Prefix Org', $this->headers())->assertOk();

        $this->assertCount(25, $response->json('data'));
    }

    public function test_lookup_routes_require_a_valid_token(): void
    {
        $org = Organization::factory()->create();
        $client = SamlClient::factory()->create(['slug' => 'acme', 'owner_id' => $org->ID]);

        $routes = [
            '/api/admin/organizations',
            "/api/admin/organizations/{$org->ID}/departments",
            "/api/admin/saml-clients/{$client->slug}/users",
            '/api/admin/systems',
        ];

        foreach ($routes as $route) {
            $this->getJson($route)->assertUnauthorized();
            $this->getJson($route, ['Authorization' => 'Bearer wrong-token'])->assertUnauthorized();
        }
    }

    public function test_systems_lookup_searches_by_name(): void
    {
        $system = System::factory()->create(['Name' => 'Mercy Health System']);
        System::factory()->create(['Name' => 'Other']);

        $this->getJson('/api/admin/systems?q=mercy', $this->headers())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $system->ID)
            ->assertJsonPath('data.0.name', 'Mercy Health System');
    }
}
