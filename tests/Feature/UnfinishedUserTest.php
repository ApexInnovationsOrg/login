<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnfinishedUserTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_guests_are_redirected_to_login()
    {
        $response = $this->get('/finishAccountCreation');

        $response->assertRedirect('/login');
    }

    public function test_unfinished_users_can_view_the_finish_screen()
    {
        $user = User::factory()->unfinished()->create();

        $response = $this->actingAs($user)->get('/finishAccountCreation');

        $response->assertStatus(200);
    }

    public function test_finished_users_are_redirected_away_from_the_finish_screen()
    {
        config(['app.mycurriculum_url' => 'http://localhost:8091/MyCurriculum.php']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/finishAccountCreation');

        $response->assertRedirect('http://localhost:8091/MyCurriculum.php');
    }

    public function test_unfinished_users_can_complete_their_account()
    {
        config(['app.mycurriculum_url' => 'http://localhost:8091/MyCurriculum.php']);
        $user = User::factory()->unfinished()->create();

        $response = $this->actingAs($user)->post('/finishUser', [
            'departmentID' => 1,
            'professionalRoleID' => 1,
            'credentialID' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('http://localhost:8091/MyCurriculum.php');

        $user->refresh();
        $this->assertEquals(1, $user->DepartmentID);
        $this->assertEquals(1, $user->CredentialID);
    }

    public function test_ems_professionals_must_provide_license_details()
    {
        $user = User::factory()->unfinished()->create();

        // ProfessionalRole 3 (EMS) requires the EMSData block
        $response = $this->actingAs($user)->post('/finishUser', [
            'departmentID' => 1,
            'professionalRoleID' => 3,
            'credentialID' => 3,
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_ems_professionals_can_complete_with_license_details()
    {
        $user = User::factory()->unfinished()->create();

        $response = $this->actingAs($user)->post('/finishUser', [
            'departmentID' => 1,
            'professionalRoleID' => 3,
            'credentialID' => 3,
            'EMSData' => [
                'stateID' => 66,
                'licenseNo' => 'TX-12345',
                'stateExpDate' => '2027-01-01',
                'NEMSID' => 'NE123',
                'NREMT' => 'NR456',
                'reregDate' => '2027-06-01',
                'licenseType' => 1,
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertEquals(66, $user->StateOfLicensureID);
        $this->assertEquals('TX-12345', $user->StateLicenseNumber);
        $this->assertEquals(3, $user->CredentialID);
        $this->assertEquals(1, $user->CredentialLicenseTypeID);
    }
}
