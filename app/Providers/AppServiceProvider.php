<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
		Validator::extend('HasNumbers', function($attribute, $value, $parameters, $validator) {
            return (1 === preg_match('~[0-9]~',$value));
        });
		
		Validator::extend('HasUppercase', function($attribute, $value, $parameters, $validator) {
            return preg_match('/[A-Z]/',$value);
        });

        Validator::extend('HasLowercase', function($attribute, $value, $parameters, $validator) {
            return preg_match('/[a-z]/',$value);
        });

        Validator::extend('HasNonAlphanumeric', function($attribute, $value, $parameters, $validator) {
            return !ctype_alnum($value);
        });
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);
	}

}
