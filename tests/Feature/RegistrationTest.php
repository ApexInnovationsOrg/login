<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Characterization: the Register.vue form posts name/email/password, but the
     * controller validates FirstName/LastName/Login/Password — so the payload the
     * real frontend sends is always rejected. Registration is broken as shipped.
     * If this test ever fails, the mismatch was fixed and the flow needs real tests.
     */
    public function test_frontend_registration_payload_is_rejected()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['FirstName', 'LastName', 'Login', 'Password']);
        $this->assertDatabaseMissing('Users', ['Login' => 'test@example.com']);
    }
}
