<?php

namespace App\Auth\Passwords;

use Illuminate\Auth\Passwords\PasswordResetServiceProvider;

class ApexPasswordResetServiceProvider extends PasswordResetServiceProvider
{

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