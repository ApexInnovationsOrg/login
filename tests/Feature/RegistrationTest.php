<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_redirects_to_the_main_site()
    {
        $response = $this->get('/register');

        $response->assertRedirect('https://www.apexinnovations.com/CreateAccountLanding.php');
    }

    public function test_registration_cannot_be_posted_here()
    {
        // Account creation happens on the main site; there is no local flow
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(405);
        $this->assertDatabaseMissing('Users', ['Login' => 'test@example.com']);
    }
}
