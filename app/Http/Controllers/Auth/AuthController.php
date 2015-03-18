<?php namespace App\Http\Controllers\Auth;

use App\Helpers\CookieMonster;
use App\Helpers\SessionManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Input;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

	use AuthenticatesAndRegistersUsers;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */

	public function __construct(Guard $auth, Registrar $registrar)
	{
        Log::info('AuthController.Init');
        $this->auth = $auth;
        $this->registrar = $registrar;

        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'Login' => 'required|email', 'Password' => 'required',
        ]);

        $credentials = $request->only('Login', 'Password');

        if ($this->auth->attempt($credentials, $request->has('remember')))
        {
            $user = $this->auth->user();
            return $this->authenticateUserSession($user->ID);
        } else {
            $user = User::where('Login', '=', Input::get('Login'))->first();

            if(isset($user)) {
                if($user->Password == md5("6#pR8@" . Input::get('Password'))) { // If their Password is still the MD5 mess

                    $this->auth->login($user);
                    $user = $this->auth->user();

                    return $this->authenticateUserSession($user->ID);
                }
                // return redirect()->intended($this->redirectPath());
            }
        }

        return redirect($this->loginPath())
            ->withInput($request->only('Login', 'remember'))
            ->withErrors([
                'Login' => $this->getFailedLoginMesssage(),
            ]);
    }

    public function getLogout()
    {
        $this->auth->logout();
        $Redis = Redis::connection();
        $user = $this->auth->user();
        Log::info('DELETE User: '.print_r($user, true));
        if($user){
            Log::info('DELETE SESSION: '.$Redis->del('User:' . $user->ID));
        }

        $response = redirect('/');
        $response = CookieMonster::removeCookieFromResponse($response, 'user-token');
        $response = CookieMonster::removeCookieFromResponse($response, Config::get('session.cookie'));
        return $response;
    }

    public function authenticateUserSession($userId) {
        $Redis = Redis::connection();
        Session::put('userId', $userId);
        Session::put('_id', Session::getId());
        $Redis->set('User:' . $userId, Session::getId());
        Log::info('authenticateUserSession: '.print_r(['session'=>Session::getId()]));
        $response = CookieMonster::addCookieToResponse(redirect()->intended($this->redirectPath()), 'user-token', $userId);
        $response = CookieMonster::addCookieToResponse($response, Config::get('session.cookie'), Session::getId());
        return $response;
    }

}
