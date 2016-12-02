<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model {


	protected $primaryKey = 'ID';
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Organizations';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	// protected $fillable = ['Name', 'Provider', 'Email'];

    public function getOrganization($email)
    {
    	$OrgID = DB::select('SELECT Organizations.ID FROM Organizations JOIN Departments ON Departments.OrganizationID = Organizations.ID AND Departments.ID = ?',[$email]);
    	
    }


}
