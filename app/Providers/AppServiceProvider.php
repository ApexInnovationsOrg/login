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
        // Override Fortify's login response here
        app()->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    // 🚀 Force Inertia to do a full browser redirect
                    return Inertia::location('https://www.apexinnovations.com/MyCurriculum.php');
                }
            };
        });
    }
}
