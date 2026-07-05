<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_with_their_login_email()
    {
        $user = User::factory()->create();

        // The form posts "email"; the controller matches it against Users.Login
        $response = $this->post('/login', [
            'email' => $user->Login,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        // Successful login hands off to the main site via Inertia::location(),
        // which responds 409 + X-Inertia-Location (the Inertia client follows it)
        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', 'https://www.apexinnovations.com/MyCurriculum.php');
    }

    public function test_login_sets_legacy_session_keys()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->Login,
            'password' => 'password',
        ]);

        // The legacy site reads both spellings of each key
        $response->assertSessionHas('userId', $user->ID);
        $response->assertSessionHas('userID', $user->ID);
        $response->assertSessionHas('userName', $user->FirstName . ' ' . $user->LastName);
        $response->assertSessionHas('Username', $user->FirstName . ' ' . $user->LastName);
    }

    public function test_login_updates_last_login_date()
    {
        $user = User::factory()->create();
        $this->assertNull($user->LastLoginDate);

        $this->post('/login', [
            'email' => $user->Login,
            'password' => 'password',
        ]);

        $this->assertNotNull($user->fresh()->LastLoginDate);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->Login,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_disabled_users_are_rejected()
    {
        $user = User::factory()->disabled()->create();

        $response = $this->post('/login', [
            'email' => $user->Login,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors();
    }

    public function test_admin_reset_users_are_forced_to_reset_password()
    {
        $user = User::factory()->adminReset()->create();

        $response = $this->post('/login', [
            'email' => $user->Login,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('reset-made-password');
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_legacy_get_logout_route_works()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/auth/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
