<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlClientModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_a_valid_client(): void
    {
        $client = SamlClient::factory()->create();

        $this->assertTrue($client->enabled);
        $this->assertTrue($client->jit_enabled);
        $this->assertIsArray($client->attribute_map);
        $this->assertArrayHasKey('email', $client->attribute_map);
    }

    public function test_slug_is_unique(): void
    {
        SamlClient::factory()->create(['slug' => 'acme']);

        $this->expectException(UniqueConstraintViolationException::class);
        SamlClient::factory()->create(['slug' => 'acme']);
    }

    public function test_urls_derive_from_slug(): void
    {
        $client = SamlClient::factory()->create(['slug' => 'acme']);

        $this->assertSame(url('/saml/acme/acs'), $client->acsUrl());
        $this->assertSame(url('/saml/acme/metadata'), $client->metadataUrl());
    }

    public function test_for_email_domain_finds_enabled_client_case_insensitively(): void
    {
        $client = SamlClient::factory()->create(['email_domains' => ['mdanderson.org']]);

        $this->assertTrue($client->is(SamlClient::forEmailDomain('MDAnderson.ORG')));
    }

    public function test_for_email_domain_ignores_disabled_clients(): void
    {
        SamlClient::factory()->create(['enabled' => false, 'email_domains' => ['mdanderson.org']]);

        $this->assertNull(SamlClient::forEmailDomain('mdanderson.org'));
    }

    public function test_for_email_domain_returns_null_when_no_client_claims_it(): void
    {
        SamlClient::factory()->create(['email_domains' => ['mdanderson.org']]);

        $this->assertNull(SamlClient::forEmailDomain('gmail.com'));
    }

    public function test_admin_portal_defaults_to_false(): void
    {
        $client = SamlClient::factory()->create();

        $this->assertFalse($client->admin_portal);
    }

    public function test_admin_portal_state_sets_flag(): void
    {
        $client = SamlClient::factory()->adminPortal()->create();

        $this->assertTrue($client->refresh()->admin_portal);
    }

    public function test_email_domain_lookup_never_matches_admin_portal_clients(): void
    {
        SamlClient::factory()->adminPortal()->create(['email_domains' => ['apexinnovations.com']]);

        $this->assertNull(SamlClient::forEmailDomain('apexinnovations.com'));
    }
}
