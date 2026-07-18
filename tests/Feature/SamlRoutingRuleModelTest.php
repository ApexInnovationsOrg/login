<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Saml\RoutingOperator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlRoutingRuleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_round_trips_as_enum(): void
    {
        $client = SamlClient::factory()->create();
        $rule = SamlDepartmentRule::factory()->create([
            'saml_client_id' => $client->id, 'operator' => RoutingOperator::NotContains,
        ]);

        $this->assertSame(RoutingOperator::NotContains, $rule->fresh()->operator);
    }

    public function test_relations_are_position_ordered(): void
    {
        $client = SamlClient::factory()->create();
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'position' => 2, 'organization_id' => 5]);
        SamlOrgRule::factory()->create(['saml_client_id' => $client->id, 'position' => 1, 'organization_id' => 4]);

        $this->assertSame([1, 2], $client->orgRules()->pluck('position')->all());
        $this->assertSame([4, 5], $client->orgRules()->pluck('organization_id')->all());
    }

    public function test_catch_all_helper_requires_the_exact_triple(): void
    {
        $client = SamlClient::factory()->create();
        $catchAll = SamlDepartmentRule::factory()->catchAll()->create(['saml_client_id' => $client->id]);
        $wildcardOnValueOnly = SamlDepartmentRule::factory()->create([
            'saml_client_id' => $client->id, 'position' => 2,
            'attribute' => 'group', 'operator' => RoutingOperator::Wildcard, 'value' => '*',
        ]);

        $this->assertTrue($catchAll->isCatchAll());
        $this->assertFalse($wildcardOnValueOnly->isCatchAll());
    }
}
