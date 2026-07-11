<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Models\System;
use App\Saml\SamlClientManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SamlClientManagerTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): SamlClientManager
    {
        return app(SamlClientManager::class);
    }

    public function test_create_with_minimal_input_slugs_the_name(): void
    {
        $org = Organization::factory()->create();

        $client = $this->manager()->create([
            'name' => 'Health System One',
            'owner_type' => 'organization',
            'owner_id' => $org->ID,
        ]);

        $this->assertSame('health-system-one', $client->slug);
        $this->assertFalse($client->enabled); // disabled until IdP metadata arrives
        $this->assertFalse($client->jit_enabled);
        $this->assertSame('pending', $client->idp_entity_id);
        $this->assertArrayHasKey('email', $client->attribute_map);
    }

    public function test_create_validates_required_fields(): void
    {
        $this->expectException(ValidationException::class);

        $this->manager()->create(['name' => '']);
    }

    public function test_create_rejects_duplicate_slug(): void
    {
        SamlClient::factory()->create(['slug' => 'acme']);

        $this->expectException(ValidationException::class);

        $this->manager()->create(['name' => 'Acme', 'slug' => 'acme', 'owner_id' => 1]);
    }

    public function test_update_from_idp_metadata_fills_idp_fields(): void
    {
        $client = SamlClient::factory()->create();
        $xml = file_get_contents(base_path('tests/Fixtures/saml/okta-idp-metadata.xml'));

        $client = $this->manager()->updateFromIdpMetadata($client, $xml);

        $this->assertSame('http://www.okta.com/exk1fixture0Okta', $client->idp_entity_id);
        $this->assertStringContainsString('MIIFIXTUREOKTACERTBODY', $client->idp_certificate);
    }

    public function test_set_enabled_toggles(): void
    {
        $client = SamlClient::factory()->create(['enabled' => false]);

        $this->assertTrue($this->manager()->setEnabled($client, true)->enabled);
        $this->assertFalse($this->manager()->setEnabled($client, false)->enabled);
    }

    public function test_certificate_status_reads_expiry(): void
    {
        // sp.crt fixture is valid for 3650 days from generation — not expiring
        $client = SamlClient::factory()->create([
            'idp_certificate' => file_get_contents(base_path('tests/Fixtures/saml/sp.crt')),
        ]);

        $status = $this->manager()->certificateStatus($client);

        $this->assertNotNull($status['expires_at']);
        $this->assertFalse($status['expiring']);
    }

    public function test_certificate_status_handles_placeholder(): void
    {
        $client = SamlClient::factory()->create(['idp_certificate' => 'pending']);

        $status = $this->manager()->certificateStatus($client);

        $this->assertNull($status['expires_at']);
        $this->assertFalse($status['expiring']);
    }

    public function test_domains_are_normalized_on_create(): void
    {
        $org = Organization::factory()->create();

        $client = $this->manager()->create([
            'name' => 'Acme', 'owner_type' => 'organization', 'owner_id' => $org->ID,
            'email_domains' => [' @MDAnderson.ORG ', 'mdanderson.org'],
        ]);

        $this->assertSame(['mdanderson.org'], $client->email_domains);
    }

    public function test_malformed_domains_are_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->manager()->create([
            'name' => 'Acme', 'owner_id' => 1,
            'email_domains' => ['not a domain'],
        ]);
    }

    public function test_a_domain_cannot_be_claimed_by_two_clients(): void
    {
        SamlClient::factory()->create(['enabled' => false, 'email_domains' => ['mdanderson.org']]);

        $this->expectException(ValidationException::class);

        $this->manager()->create([
            'name' => 'Acme', 'owner_id' => 1,
            'email_domains' => ['mdanderson.org'],
        ]);
    }

    public function test_update_replaces_domains_and_allows_keeping_own(): void
    {
        $client = SamlClient::factory()->create(['email_domains' => ['mdanderson.org']]);

        $updated = $this->manager()->update($client, [
            'email_domains' => ['mdanderson.org', 'mdacc.org'],
        ]);

        $this->assertSame(['mdanderson.org', 'mdacc.org'], $updated->email_domains);
    }

    public function test_update_with_explicit_null_department_id_clears_it(): void
    {
        $dept = Department::factory()->create();
        $client = SamlClient::factory()->create(['owner_id' => $dept->OrganizationID, 'department_id' => $dept->ID]);

        $updated = $this->manager()->update($client, ['department_id' => null]);

        $this->assertNull($updated->department_id);
    }

    public function test_create_rejects_unknown_owner(): void
    {
        $this->expectException(ValidationException::class);

        app(SamlClientManager::class)->create(['name' => 'X', 'owner_type' => 'organization', 'owner_id' => 999999]);
    }

    public function test_default_department_must_belong_to_owning_org(): void
    {
        $org = Organization::factory()->create();
        $otherOrgDept = Department::factory()->create(); // factory mints its own org

        $this->expectException(ValidationException::class);

        app(SamlClientManager::class)->create([
            'name' => 'X', 'owner_type' => 'organization', 'owner_id' => $org->ID,
            'department_id' => $otherOrgDept->ID,
        ]);
    }

    public function test_reparent_to_system_with_default_department_is_rejected(): void
    {
        $dept = Department::factory()->create();
        $client = SamlClient::factory()->create(['owner_id' => $dept->OrganizationID, 'department_id' => $dept->ID]);
        $system = System::factory()->create();

        $this->expectException(ValidationException::class);

        app(SamlClientManager::class)->update($client, ['owner_type' => 'system', 'owner_id' => $system->ID]);
    }

    public function test_update_rejects_owner_type_without_owner_id(): void
    {
        $client = SamlClient::factory()->create();

        $this->expectException(ValidationException::class);

        app(SamlClientManager::class)->update($client, ['owner_type' => 'system']);
    }

    public function test_update_rejects_owner_id_without_owner_type(): void
    {
        $client = SamlClient::factory()->create();

        $this->expectException(ValidationException::class);

        app(SamlClientManager::class)->update($client, ['owner_id' => 42]);
    }

    public function test_reparent_with_org_rules_present_is_rejected(): void
    {
        $system = System::factory()->create();
        $client = SamlClient::factory()->forSystem($system->ID)->create();
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id]);
        $otherSystem = System::factory()->create();

        try {
            app(SamlClientManager::class)->update($client, ['owner_type' => 'system', 'owner_id' => $otherSystem->ID]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('owner_id', $e->errors());
            $this->assertSame('Clear routing rules before re-parenting this client.', $e->errors()['owner_id'][0]);
        }
    }

    public function test_reparent_with_department_rules_present_is_rejected(): void
    {
        $org = Organization::factory()->create();
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $client->id]);
        $otherOrg = Organization::factory()->create();

        try {
            app(SamlClientManager::class)->update($client, ['owner_type' => 'organization', 'owner_id' => $otherOrg->ID]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('owner_id', $e->errors());
            $this->assertSame('Clear routing rules before re-parenting this client.', $e->errors()['owner_id'][0]);
        }
    }

    public function test_reparent_after_clearing_rules_succeeds(): void
    {
        $system = System::factory()->create();
        $client = SamlClient::factory()->forSystem($system->ID)->create();
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id]);
        $otherSystem = System::factory()->create();

        DB::table('saml_org_rules')->where('saml_client_id', $client->id)->delete();

        $updated = app(SamlClientManager::class)->update($client, ['owner_type' => 'system', 'owner_id' => $otherSystem->ID]);

        $this->assertSame($otherSystem->ID, $updated->owner_id);
    }

    public function test_non_owner_update_with_rules_present_is_unaffected(): void
    {
        $org = Organization::factory()->create();
        $client = SamlClient::factory()->create(['owner_id' => $org->ID, 'name' => 'Old Name']);
        SamlDepartmentRule::factory()->create(['saml_client_id' => $client->id]);

        $updated = app(SamlClientManager::class)->update($client, ['name' => 'New Name']);

        $this->assertSame('New Name', $updated->name);
    }

    public function test_known_attributes_are_trimmed_and_deduped(): void
    {
        $client = SamlClient::factory()->create();

        app(SamlClientManager::class)->update($client, ['known_attributes' => [' department ', 'department', 'groups']]);

        $this->assertSame(['department', 'groups'], $client->fresh()->known_attributes);
    }

    public function test_known_attributes_reject_non_string_entries(): void
    {
        $client = SamlClient::factory()->create();
        $this->expectException(ValidationException::class);

        app(SamlClientManager::class)->update($client, ['known_attributes' => ['ok', 123]]);
    }
}
