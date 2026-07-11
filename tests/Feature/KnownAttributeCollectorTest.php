<?php

namespace Tests\Feature;

use App\Models\SamlAttributeObservation;
use App\Models\SamlClient;
use App\Saml\KnownAttributeCollector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class KnownAttributeCollectorTest extends TestCase
{
    use RefreshDatabase;

    private KnownAttributeCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collector = app(KnownAttributeCollector::class);
    }

    private function assertion(): array
    {
        // php-saml getAttributes() shape: name => [values]. Values are PHI in
        // real life; the collector must ignore them entirely.
        return [
            'email' => ['jane@acme.test'],
            'firstName' => ['Jane'],
            'lastName' => ['Doe'],
            'department' => ['Cardiology'],
            'eduPersonAffiliation' => ['staff', 'member'],
        ];
    }

    public function test_captures_non_identity_names_only(): void
    {
        $client = SamlClient::factory()->create([
            'attribute_map' => ['email' => 'email', 'first_name' => 'firstName', 'last_name' => 'lastName'],
            'known_attributes' => [],
        ]);

        $this->collector->capture($client, array_keys($this->assertion()));

        $this->assertEqualsCanonicalizing(['department', 'eduPersonAffiliation'], $client->fresh()->known_attributes);
    }

    public function test_never_persists_a_value(): void
    {
        $client = SamlClient::factory()->create(['attribute_map' => ['email' => 'email', 'first_name' => 'firstName', 'last_name' => 'lastName']]);

        $this->collector->capture($client, array_keys($this->assertion()));

        // No stored string anywhere equals an asserted VALUE.
        foreach (['jane@acme.test', 'Jane', 'Doe', 'Cardiology', 'staff', 'member'] as $value) {
            $this->assertDatabaseMissing('saml_attribute_observations', ['name' => $value]);
        }
        $this->assertNotContains('Cardiology', $client->fresh()->known_attributes);
    }

    public function test_upserts_observations_with_counts(): void
    {
        $client = SamlClient::factory()->create(['attribute_map' => ['email' => 'email', 'first_name' => 'firstName', 'last_name' => 'lastName']]);

        $this->collector->capture($client, array_keys($this->assertion()));
        $this->collector->capture($client, array_keys($this->assertion()));

        $obs = SamlAttributeObservation::where('saml_client_id', $client->id)->where('name', 'department')->first();
        $this->assertSame(2, $obs->observation_count);
        $this->assertNotNull($obs->first_seen_at);
    }

    public function test_no_saml_clients_write_when_nothing_new(): void
    {
        $client = SamlClient::factory()->create([
            'attribute_map' => ['email' => 'email', 'first_name' => 'firstName', 'last_name' => 'lastName'],
            'known_attributes' => ['department', 'eduPersonAffiliation'],
        ]);
        $before = $client->updated_at;
        $this->travel(1)->minutes();

        $this->collector->capture($client, array_keys($this->assertion()));

        $this->assertEquals($before, $client->fresh()->updated_at); // column untouched
    }

    public function test_admin_portal_clients_are_skipped(): void
    {
        $client = SamlClient::factory()->adminPortal()->create();

        $this->collector->capture($client, array_keys($this->assertion()));

        $this->assertSame([], $client->fresh()->known_attributes);
        $this->assertDatabaseCount('saml_attribute_observations', 0);
    }

    public function test_capture_swallows_and_logs_a_real_failure(): void
    {
        Log::spy();
        $client = SamlClient::factory()->create([
            'attribute_map' => ['email' => 'email', 'first_name' => 'firstName', 'last_name' => 'lastName'],
        ]);

        // Force a genuine throw from inside capture. The implementation uses
        // an atomic upsert() (no model events fire), so drop the schema
        // underneath it to make the query itself fail — this exercises the
        // real code path the collector executes, not a model event hook.
        \Illuminate\Support\Facades\Schema::drop('saml_attribute_observations');

        // Must NOT throw out of capture (a capture failure can never break a login).
        $this->collector->capture($client, array_keys($this->assertion()));

        // Verify the warning was logged with the correct message and context.
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn ($message, $context = []) => $message === 'known-attribute capture failed'
                && ($context['client'] ?? null) === $client->slug
                && str_contains($context['error'] ?? '', 'saml_attribute_observations'));
    }
}
