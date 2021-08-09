<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use App\Rules\HasNumbers;
use App\Rules\HasLowercase;
use App\Rules\HasUppercase;
use App\Rules\HasNonAlphanumeric;


class User extends Authenticatable
{
    use HasFactory, Notifiable;
    
    public $timestamps = false;
    protected $primaryKey = 'ID';
    protected $table = 'Users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['Login', 
    'FirstName',
     'Password',
     'PreviousLastLoginDate',
     'LastLoginDate',
     'LastName',
      'Address',
      'Address2',
      'City', 
      'StateID', 
      'CountryID', 
      'DepartmentID', 
      'LMS', 
      'Active', 
      'Beta', 
      'ShowDemoReporting', 
      'PasswordChangedByAdmin', 
      'Locale', 
      'oldUser'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'Password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];




    public function id()
    {
        return $this->ID;
    }

    public function userId()
    {
        return $this->ID;
    }

    public function getEmailForPasswordReset()
    {
        return $this->Login;
    }

    public function getAuthIdentifier()
    {
        return $this->ID;
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function sendPasswordResetNotification($token)
    {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));
        Mail::to($this->Login)->send(new ResetPassword($url,$this));
    }
    public function department()
    {
        return $this->hasOne(Department::class,'ID','DepartmentID');
    }

    public function getPasswordRequirements()
    {
		$Org = $this->department->org;

		
		$requirementTypes = ["PasswordMinLength", "PasswordComplexityNumeric", "PasswordComplexitySpecial", "PasswordComplexityUppercase", "PasswordComplexityLowercase"];
        $requirements = ['required','confirmed'];

		foreach ($requirementTypes as $requirement) 
		{
			switch($requirement)
			{
				case "PasswordMinLength":
					$requirements[] = 'min:' . $Org->PasswordMinLength;
					break;
				case "PasswordComplexityNumeric":
					if($Org->PasswordComplexityNumeric == "Y")
					{
						$requirements[] = new HasNumbers;
					}
					break;
				case "PasswordComplexitySpecial":
					if($Org->PasswordComplexitySpecial == "Y")
					{
						$requirements[] = new HasNonAlphanumeric;
					}
					break;
				case "PasswordComplexityUppercase":
					if($Org->PasswordComplexityUppercase == "Y")
					{
						$requirements[] = new HasUppercase;
					}
					break;
				case "PasswordComplexityLowercase":
					if($Org->PasswordComplexityLowercase == "Y")
					{
						$requirements[] = new HasLowercase;
					}
					break;
			}

		}

		return $requirements;
    }
}
