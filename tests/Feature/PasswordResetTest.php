<?php

namespace Tests\Feature;

use App\Mail\ResetPassword;
use App\Models\SamlClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_reset_password_link_screen_can_be_rendered()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested()
    {
        // The app sends a Mailable directly (not a Notification)
        Mail::fake();

        $user = User::factory()->create();

        // The forgot-password form posts "Login"
        $response = $this->post('/forgot-password', ['Login' => $user->Login]);

        $response->assertSessionHasNoErrors();
        Mail::assertSent(ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->Login);
        });
        // The custom token repository keys password_resets on Login, not email
        $this->assertDatabaseHas('password_resets', ['Login' => $user->Login]);
    }

    public function test_sso_domain_emails_cannot_request_password_reset(): void
    {
        Mail::fake();

        SamlClient::factory()->create(['email_domains' => ['acme.com']]);
        $user = User::factory()->create(['Login' => 'jane@acme.com']);

        $response = $this->post('/forgot-password', ['Login' => 'jane@acme.com']);

        $response->assertSessionHasErrors('email');
        Mail::assertNotSent(ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->Login);
        });
    }

    public function test_reset_password_screen_can_be_rendered()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get('/reset-password/'.$token.'?email='.urlencode($user->Login));

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'Login' => $user->Login,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('new-password', $user->fresh()->Password));
    }

    public function test_user_can_login_with_reset_password()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'Login' => $user->Login,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->Login,
            'password' => 'new-password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_password_reset_is_rejected_with_invalid_token()
    {
        $user = User::factory()->create();
        Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => 'not-the-real-token',
            'Login' => $user->Login,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertFalse(Hash::check('new-password', $user->fresh()->Password));
    }

    public function test_organization_password_policy_is_enforced()
    {
        // Department 2 belongs to the seeded strict org: min 12 + all complexity rules
        $user = User::factory()->create(['DepartmentID' => 2]);
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'Login' => $user->Login,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertFalse(Hash::check('short', $user->fresh()->Password));
    }
}
