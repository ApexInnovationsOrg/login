<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\User as ApexUser;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Support\Str;
use Illuminate\Auth\SessionGuard;
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function (Saml2LoginEvent $event) {
            $messageId = $event->getSaml2Auth()->getLastMessageId();
            // Add your own code preventing reuse of a $messageId to stop replay attacks
        
            $user = $event->getSaml2User();
            $userData = [
                'id' => $user->getUserId(),
                'attributes' => $user->getAttributes(),
                'assertion' => $user->getRawSamlAssertion()
            ];

            $laravelUser = ApexUser::where('Login',$userData['id'])->first();
            // dd($laravelUser);
            Session::put('userId',$laravelUser->ID);
            Session::put('userID',$laravelUser->ID);
            Session::put('userName',$laravelUser->FirstName . ' ' . $laravelUser->LastName);
            Session::put('Username',$laravelUser->FirstName . ' ' . $laravelUser->LastName);
            //if it does not exist create it and go on  or show an error message
            // event(new LoginEvent(SessionGuard::class, $laravelUser, false));
            error_log('saml login hit');
            Auth::login($laravelUser);
            
        });
    }
}
