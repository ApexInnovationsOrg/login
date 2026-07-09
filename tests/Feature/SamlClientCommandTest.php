<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlClientCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_prints_urls(): void
    {
        $this->artisan('saml:client', ['action' => 'create', '--name' => 'Acme Health', '--org' => 1])
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
}
