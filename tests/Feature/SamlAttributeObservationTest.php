<?php

namespace Tests\Feature;

use App\Models\SamlAttributeObservation;
use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SamlAttributeObservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_attributes_defaults_to_empty_array(): void
    {
        $this->assertSame([], SamlClient::factory()->create()->known_attributes);
    }

    public function test_known_attributes_round_trips_as_array(): void
    {
        $client = SamlClient::factory()->create(['known_attributes' => ['department', 'groups']]);

        $this->assertSame(['department', 'groups'], $client->fresh()->known_attributes);
    }

    public function test_null_known_attributes_column_reads_as_empty_array(): void
    {
        $client = SamlClient::factory()->create();
        // Force a real NULL in the column (the factory always sets []).
        DB::table('saml_clients')->where('id', $client->id)->update(['known_attributes' => null]);

        $this->assertSame([], $client->fresh()->known_attributes);
    }

    public function test_observations_belong_to_a_client_and_cast(): void
    {
        $client = SamlClient::factory()->create();
        SamlAttributeObservation::factory()->create([
            'saml_client_id' => $client->id, 'name' => 'eduPersonAffiliation', 'observation_count' => 3,
        ]);

        $obs = $client->attributeObservations()->first();
        $this->assertSame('eduPersonAffiliation', $obs->name);
        $this->assertSame(3, $obs->observation_count);
        $this->assertNotNull($obs->last_seen_at);
    }
}
