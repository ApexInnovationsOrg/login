<?php namespace App\Auth;

use Illuminate\Support\Facades\Facade;
/**
 * @see \Illuminate\Auth\AuthManager
 * @see \Illuminate\Auth\Guard
 */
class Auth extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'auth'; }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(UserContract $user, $remember = false)
    {
        Log::info('login');
        $user->id = $user->ID;
        $this->updateSession($user->getAuthIdentifier());

        // If the user should be permanently "remembered" by the application we will
        // queue a permanent cookie that contains the encrypted copy of the user
        // identifier. We will then decrypt this later to retrieve the users.
        if ($remember)
        {
            $this->createRememberTokenIfDoesntExist($user);

            $this->queueRecallerCookie($user);
        }

        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        Log::info('logout');
        $user = $this->user();

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        $this->clearUserDataFromStorage();

        if ( ! is_null($this->user))
        {
            $this->refreshRememberToken($user);
        }

        if (isset($this->events))
        {
            $this->events->fire('auth.logout', [$user]);
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

}
