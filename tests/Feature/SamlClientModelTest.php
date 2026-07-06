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
}
