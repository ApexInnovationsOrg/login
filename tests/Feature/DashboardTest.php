<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_are_forwarded_to_the_main_site()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        // Inertia::location() responds 409 + X-Inertia-Location for the Inertia client
        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', 'https://www.apexinnovations.com/MyCurriculum.php');
    }

    public function test_saml_sessions_are_forwarded_with_a_plain_redirect()
    {
        // SAML flows can't follow Inertia::location; the route uses redirect()->away()
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['SAML' => true])
            ->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('https://www.apexinnovations.com/MyCurriculum.php');
    }
}
