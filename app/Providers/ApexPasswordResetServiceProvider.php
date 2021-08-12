<?php

namespace App\Providers;

use Illuminate\Auth\Passwords\PasswordResetServiceProvider;
use App\Auth\Passwords\ApexPasswordBrokerManager;
use Illuminate\Validation\Validator;

class ApexPasswordResetServiceProvider extends PasswordResetServiceProvider
{

    public function boot()
    {

    }
    // Override the method registerPasswordBroker
    // in order to specify your customized manager
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new ApexPasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }
}