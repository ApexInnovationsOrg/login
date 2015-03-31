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

Route::get('/', 'LoginController@login');
Route::get('/auth/Social','SocialLoginController@index');
Route::post('/auth/Social','SocialLoginController@show');
Route::post('/auth/Social/differentAccount','SocialLoginController@linkDifferentAccount');
Route::post('/auth/Social/verifyEmail','SocialLoginController@verifyEmail');
Route::post('/auth/Social/register','SocialLoginController@register');

Route::post('/auth/Social/email','SocialLoginController@sendAuthorizationEmail');
Route::get('/auth/Social/link','SocialLoginController@createLink');


//going to be used when we switch the account creation process over to laravel
//Route::post('/auth/Social/register','SocialLoginController@landing');



Route::get('home', ['as'=>'home', 'uses'=>'LoginController@index']);
// Route::get('test', 'HomeController@test');

// Route::get('/test', function()
// {
//     $user = App\User::where('ID', '=', '306842')->first();
//     Illuminate\Support\Facades\Auth::login($user);
//     //dd(Illuminate\Support\Facades\Auth::user());
//     $user = Illuminate\Support\Facades\Auth::user();
//     // dd($user);
//     return Redirect::action('LoginController@index', array('user' => $user->ID));
// });

// Log::info('Route::controllers Auth\AuthController');
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);