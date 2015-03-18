<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialLogins extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'SocialLogins';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['UserID', 'Provider', 'Email'];



}
