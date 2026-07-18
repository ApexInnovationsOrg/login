<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SamlClientOwnershipTest extends TestCase
{
    use RefreshDatabase;

    // First-alphabetical RefreshDatabase caution does not apply (SamlClient…
    // sorts after AdminApiSsoHandoffTest, which owns the seed pass).

    public function test_factory_default_is_org_owned(): void
    {
        $client = SamlClient::factory()->create();

        $this->assertTrue($client->ownedByOrganization());
        $this->assertSame('organization', $client->owner_type);
        $this->assertSame([$client->owner_id], $client->scopedOrganizationIds());
    }

    public function test_org_owner_name_resolves(): void
    {
        $org = Organization::factory()->create();
        $client = SamlClient::factory()->create(['owner_id' => $org->ID]);

        $this->assertSame($org->Name, $client->ownerName());
    }

    public function test_system_owned_scope_is_the_systems_organizations(): void
    {
        $system = System::factory()->create();
        $orgs = Organization::factory()->count(2)->create();
        foreach ($orgs as $org) {
            DB::table('SystemOrganizations')->insert(['SystemID' => $system->ID, 'OrganizationID' => $org->ID]);
        }

        $client = SamlClient::factory()->forSystem($system->ID)->create();

        $this->assertFalse($client->ownedByOrganization());
        $this->assertSame($system->Name, $client->ownerName());
        $this->assertEqualsCanonicalizing($orgs->pluck('ID')->all(), $client->scopedOrganizationIds());
    }

    public function test_system_owned_with_no_orgs_has_empty_scope(): void
    {
        $system = System::factory()->create();
        $client = SamlClient::factory()->forSystem($system->ID)->create();

        $this->assertSame([], $client->scopedOrganizationIds());
    }
}
