<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Providers extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Providers';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['Name'];


}
