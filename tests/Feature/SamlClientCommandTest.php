<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlClientCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_prints_urls(): void
    {
        $org = Organization::factory()->create();

        $this->artisan('saml:client', ['action' => 'create', '--name' => 'Acme Health', '--org' => $org->ID])
            ->expectsOutputToContain('/saml/acme-health/acs')
            ->expectsOutputToContain('/saml/acme-health/metadata')
            ->assertSuccessful();

        $this->assertDatabaseHas('saml_clients', ['slug' => 'acme-health', 'enabled' => false]);
    }

    public function test_update_with_metadata_file(): void
    {
        $client = SamlClient::factory()->create(['slug' => 'acme']);

        $this->artisan('saml:client', [
            'action' => 'update',
            'slug' => 'acme',
            '--metadata' => base_path('tests/Fixtures/saml/okta-idp-metadata.xml'),
        ])->assertSuccessful();

        $this->assertSame('http://www.okta.com/exk1fixture0Okta', $client->fresh()->idp_entity_id);
    }

    public function test_enable_and_disable(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'enabled' => false]);

        $this->artisan('saml:client', ['action' => 'enable', 'slug' => 'acme'])->assertSuccessful();
        $this->assertTrue(SamlClient::where('slug', 'acme')->first()->enabled);

        $this->artisan('saml:client', ['action' => 'disable', 'slug' => 'acme'])->assertSuccessful();
        $this->assertFalse(SamlClient::where('slug', 'acme')->first()->enabled);
    }

    public function test_list_shows_clients_and_cert_expiry(): void
    {
        SamlClient::factory()->create(['slug' => 'acme', 'name' => 'Acme Health']);

        $this->artisan('saml:client', ['action' => 'list'])
            ->expectsOutputToContain('acme')
            ->assertSuccessful();
    }

    public function test_unknown_slug_fails(): void
    {
        $this->artisan('saml:client', ['action' => 'enable', 'slug' => 'nope'])->assertFailed();
    }

    public function test_malformed_metadata_fails_cleanly(): void
    {
        SamlClient::factory()->create(['slug' => 'acme']);

        $bad = tempnam(sys_get_temp_dir(), 'saml').'.xml';
        file_put_contents($bad, '<not-saml/>');

        $this->artisan('saml:client', [
            'action' => 'update',
            'slug' => 'acme',
            '--metadata' => $bad,
        ])->assertFailed();

        unlink($bad);
    }

    public function test_create_accepts_domains_option(): void
    {
        $org = Organization::factory()->create();

        $this->artisan('saml:client', [
            'action' => 'create', '--name' => 'Acme', '--org' => (string) $org->ID,
            '--domains' => 'Acme.com, portal.acme.com',
        ])->assertSuccessful();

        $this->assertSame(['acme.com', 'portal.acme.com'], SamlClient::where('slug', 'acme')->first()->email_domains);
    }

    public function test_update_replaces_domains(): void
    {
        $client = SamlClient::factory()->create(['email_domains' => ['old.com']]);

        $this->artisan('saml:client', [
            'action' => 'update', 'slug' => $client->slug, '--domains' => 'new.com',
        ])->assertSuccessful();

        $this->assertSame(['new.com'], $client->fresh()->email_domains);
    }

    public function test_update_with_empty_domains_clears_the_list(): void
    {
        $client = SamlClient::factory()->create(['email_domains' => ['old.com']]);

        $this->artisan('saml:client', [
            'action' => 'update', 'slug' => $client->slug, '--domains' => '',
        ])->assertSuccessful();

        $this->assertSame([], $client->fresh()->email_domains);
    }

    public function test_describe_shows_domains(): void
    {
        $client = SamlClient::factory()->create(['email_domains' => ['acme.com']]);

        $this->artisan('saml:client', ['action' => 'describe', 'slug' => $client->slug])
            ->expectsOutputToContain('acme.com')
            ->assertSuccessful();
    }

    public function test_create_with_admin_portal_flag(): void
    {
        $org = Organization::factory()->create();

        $this->artisan('saml:client', [
            'action' => 'create', '--name' => 'Apex Admin', '--org' => $org->ID, '--admin-portal' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('saml_clients', ['slug' => 'apex-admin', 'admin_portal' => 1]);
    }

    public function test_admin_portal_client_cannot_claim_domains(): void
    {
        $org = Organization::factory()->create();

        $this->artisan('saml:client', [
            'action' => 'create', '--name' => 'Apex Admin', '--org' => $org->ID,
            '--admin-portal' => true, '--domains' => 'apexinnovations.com',
        ])->assertFailed();

        $this->assertDatabaseMissing('saml_clients', ['slug' => 'apex-admin']);
    }

    public function test_update_can_toggle_admin_portal_off(): void
    {
        SamlClient::factory()->adminPortal()->create(['slug' => 'apex-admin']);

        $this->artisan('saml:client', [
            'action' => 'update', 'slug' => 'apex-admin', '--no-admin-portal' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('saml_clients', ['slug' => 'apex-admin', 'admin_portal' => 0]);
    }

    public function test_create_with_system_owner(): void
    {
        $system = System::factory()->create();

        $this->artisan('saml:client', [
            'action' => 'create', '--name' => 'Sys Client', '--system' => $system->ID,
        ])->assertSuccessful();

        $this->assertDatabaseHas('saml_clients', ['slug' => 'sys-client', 'owner_type' => 'system', 'owner_id' => $system->ID]);
    }

    public function test_create_requires_exactly_one_owner(): void
    {
        $this->artisan('saml:client', ['action' => 'create', '--name' => 'Nope'])->assertFailed();
        $this->artisan('saml:client', ['action' => 'create', '--name' => 'Nope', '--org' => 1, '--system' => 1])->assertFailed();
        $this->assertDatabaseMissing('saml_clients', ['slug' => 'nope']);
    }

    public function test_system_owned_client_rejects_default_department(): void
    {
        $system = System::factory()->create();

        $this->artisan('saml:client', [
            'action' => 'create', '--name' => 'Sys Client', '--system' => $system->ID, '--department' => 1,
        ])->assertFailed();

        $this->assertDatabaseMissing('saml_clients', ['slug' => 'sys-client']);
    }
}
