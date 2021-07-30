<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


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

    protected $primaryKey = 'ID';

    public function getEmailForPasswordReset()
    {
        return $this->Login;
    }

    public function getAuthIdentifier()
    {
        return $this->Login;
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }
}
