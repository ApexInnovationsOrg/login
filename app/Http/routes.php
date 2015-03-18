<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'LoginController@index');

Route::get('home', ['as'=>'home', 'uses'=>'LoginController@index']);
Route::get('test', 'HomeController@test');

Route::get('/test', function()
{
    $user = App\User::where('ID', '=', '306842')->first();
    Illuminate\Support\Facades\Auth::login($user);
    //dd(Illuminate\Support\Facades\Auth::user());
    $user = Illuminate\Support\Facades\Auth::user();
    // dd($user);
    return Redirect::action('LoginController@index', array('user' => $user->ID));
});

// Log::info('Route::controllers Auth\AuthController');
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);