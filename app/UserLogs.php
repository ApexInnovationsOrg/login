<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLogs extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'UserLogs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['UserID', 'EventID', 'Info'];



}
