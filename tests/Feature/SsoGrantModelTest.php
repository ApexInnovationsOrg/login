<?php

namespace Tests\Feature;

use App\Models\SsoGrant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoGrantModelTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_grant_links_user_and_enforces_uniqueness(): void
    {
        $user = User::factory()->create();

        $grant = SsoGrant::create([
            'user_id' => $user->ID,
            'owner_type' => 'organization',
            'owner_id' => 933,
            'granted_by' => '1:Test Admin',
        ]);

        $this->assertTrue($grant->user->is($user));

        $this->expectException(QueryException::class);

        SsoGrant::create([
            'user_id' => $user->ID,
            'owner_type' => 'organization',
            'owner_id' => 933,
            'granted_by' => '2:Other Admin',
        ]);
    }
}
