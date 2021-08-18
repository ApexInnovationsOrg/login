<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\User as ApexUser;
use Carbon\Carbon;
use Exception;
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
            if($event->getSaml2Idp() == "MDA")
            {
                $humanAttributes = $user->getAttributesWithFriendlyName();
                
                if(empty($humanAttributes['mail'])) // no email provided
                {
                   return abort(401);
                }
                $laravelUser = ApexUser::firstOrNew(['Login'=>$humanAttributes['mail'][0]]);
                
                $laravelUser->FirstName = empty($humanAttributes['givenName']) ? 'FirstName' : $humanAttributes['givenName'][0];
                $laravelUser->LastName = empty($humanAttributes['sn']) ? 'LastName' : $humanAttributes['sn'][0];
                $laravelUser->Address = "1515 Holcombe Blvd";
                $laravelUser->City = "Houston";
                $laravelUser->PostalCode = "77030";
                $laravelUser->StateID = 66;
                $laravelUser->CredentialID = 0;
                Session::put('Organization',933);
                $laravelUser->CreationDate = Carbon::now();
                $laravelUser->Active = 'Y';
                $laravelUser->Disabled = 'N';
                $laravelUser->PasswordChangedByAdmin = 'N';
                $laravelUser->LMS = 'N';
                $laravelUser->Locale = 'en-us';
                $laravelUser->EmployeeID = empty($humanAttributes['workforceID']) ? 0000000 : $humanAttributes['workforceID'][0];
            }

            if($event->getSaml2Idp() == "APEX")
            {

                $laravelUser = ApexUser::where('Login',$userData['id'])->first();
                Session::put('Organization',933);
            }
            
            Session::put('SAML',true);
            Session::put('userId',$laravelUser->ID);
            Session::put('userID',$laravelUser->ID);
            Session::put('userName',$laravelUser->FirstName . ' ' . $laravelUser->LastName);
            Session::put('Username',$laravelUser->FirstName . ' ' . $laravelUser->LastName);
            //if it does not exist create it and go on  or show an error message
            $laravelUser->LastLoginDate = Carbon::now();
            $laravelUser->save(); 
            Auth::login($laravelUser);
            
        });

        Event::listen('Aacotroneo\Saml2\Events\Saml2LogoutEvent', function ($event) {
            Auth::logout();
            Session::save();
        });
    }
}
