<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_guests_cannot_view_the_forced_reset_screen()
    {
        $response = $this->get('/reset-made-password');

        $response->assertRedirect('/login');
    }

    public function test_forced_reset_screen_can_be_rendered()
    {
        $user = User::factory()->adminReset()->create();

        $response = $this->actingAs($user)->get('/reset-made-password');

        $response->assertStatus(200);
    }

    public function test_forced_reset_updates_password_and_clears_flag()
    {
        $user = User::factory()->adminReset()->create();

        $response = $this->actingAs($user)->post('/reset-made-password', [
            'Login' => $user->Login,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertSessionHasNoErrors();
        // On success the user is handed off to the main site via Inertia::location()
        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', 'https://www.apexinnovations.com/MyCurriculum.php');

        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-password', $user->Password));
        $this->assertEquals('N', $user->PasswordChangedByAdmin);
    }

    public function test_forced_reset_enforces_organization_password_policy()
    {
        // Department 2 belongs to the seeded strict org: min 12 + all complexity rules
        $user = User::factory()->adminReset()->create(['DepartmentID' => 2]);

        $response = $this->actingAs($user)->post('/reset-made-password', [
            'Login' => $user->Login,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('Y', $user->fresh()->PasswordChangedByAdmin);
    }
}
