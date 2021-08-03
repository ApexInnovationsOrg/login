<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cookie\CookieJar;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
         
        $this->app->singleton('cookie', function ($app) {
            $config = $app['config']['session'];

            return (new CookieJar)->setDefaultPathAndDomain($config['path'], $config['domain']);
        });
        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
