<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'Login' => $this->faker->unique()->safeEmail(),
            'Password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'FirstName' => $this->faker->firstName(),
            'LastName' => $this->faker->lastName(),
            'Address' => $this->faker->streetAddress(),
            'City' => $this->faker->city(),
            'StateID' => 1,
            'PostalCode' => $this->faker->postcode(),
            'CountryID' => 1,
            'Phone' => $this->faker->phoneNumber(),
            'CreationDate' => now()->format('Y-m-d H:i:s'),
            'DepartmentID' => 1,
            'CredentialID' => 1,
            'SecurityAnswer' => '',
            'Locale' => 'en-us',
            'Active' => 'Y',
            'Disabled' => 'N',
            'PasswordChangedByAdmin' => 'N',
            'LMS' => 'N',
        ];
    }

    /**
     * A user provisioned by SSO who has not completed account creation
     * (routed to /finishAccountCreation by the unfinishedUser middleware).
     *
     * @return Factory
     */
    public function unfinished()
    {
        return $this->state(function (array $attributes) {
            return [
                // SSO-provisioned users get 0 (DepartmentID is NOT NULL in prod);
                // the unfinishedUser middleware's loose == null check matches 0
                'DepartmentID' => 0,
                'CredentialID' => 0,
            ];
        });
    }

    /**
     * A disabled account (login is refused).
     *
     * @return Factory
     */
    public function disabled()
    {
        return $this->state(function (array $attributes) {
            return [
                'Disabled' => 'Y',
            ];
        });
    }

    /**
     * An account whose password was reset by an admin
     * (forced to /reset-made-password after login).
     *
     * @return Factory
     */
    public function adminReset()
    {
        return $this->state(function (array $attributes) {
            return [
                'PasswordChangedByAdmin' => 'Y',
            ];
        });
    }
}
