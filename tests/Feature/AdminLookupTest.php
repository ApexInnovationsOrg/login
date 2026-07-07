<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $client = SamlClient::factory()->create(['slug' => 'acme', 'organization_id' => $dept->OrganizationID]);
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
}
