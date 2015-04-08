<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;
    public $timestamps = false;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['Login', 'FirstName', 'Password','LastLoginDate','LastName', 'Address', 'Address2', 'City', 'StateID', 'CountryID', 'DepartmentID', 'LMS', 'Active', 'Beta', 'ShowDemoReporting', 'PasswordChangedByAdmin', 'Locale', 'oldUser']
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['Password'];


	protected $primaryKey = 'ID';


    public function swapping($user) {
        $LastSessionID   = Session::getId(); //get new session_id after user sign in
        $last_session = Session::getHandler()->read($user->LastSessionID); // retrive last session

        if ($last_session) {
            if (Session::getHandler()->destroy(LastSessionID)) {
                // session was destroyed
            }
        }

        $user->LastSessionID = $new_sessid;
        $user->save();
    }

}
