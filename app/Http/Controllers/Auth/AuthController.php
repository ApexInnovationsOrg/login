<?php namespace App\Http\Controllers\Auth;

use App\Helpers\CookieMonster;
use App\Helpers\Logger;
use App\Helpers\SessionManager;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\User;
use Crypt;

use App\Providers;
use App\SocialLogins;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
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
            'EmailLogin' => 'required|email', 'Password' => 'required',
        ]);

        $credentials = $request->only('EmailLogin', 'Password');

        $user = User::where('Login', '=', $credentials['EmailLogin'])->first();
        if ($user && Hash::check($credentials['Password'],$user->Password))
        { 
           
            $logInfo = ['SERVER'=>$_SERVER,'Password'=>'bcrypt'];
            $log = new Logger(json_encode($logInfo),1,$user->ID);
            $log->SaveLog();
            
            if(Input::get('providerName') != null)
            {
                $this->linkSocialMedia(Input::get('providerName'),Input::get('email'),$user); 
            }          
            return SessionHelper::authenticateUserSession($user->ID);
        } else {
            $user = User::where('Login', '=', Input::get('EmailLogin'))->first();

            if(isset($user)) {
                if($user->Password == md5("6#pR8@" . Input::get('Password'))) 
                { // If their Password is still the MD5 mess

                  
                    $logInfo = ['SERVER'=>$_SERVER,'Password'=>'md5'];
                    $log = new Logger(json_encode($logInfo),1,$user->ID);
                    $log->SaveLog();
                    if(Input::get('providerName') != null)
                    {
                         $this->linkSocialMedia(Input::get('providerName'),Input::get('email'),$user); 
                    }

                    return SessionHelper::authenticateUserSession($user->ID);
                }
            }
        }
        $userID = isset($user) ? $user->ID : 0;
        $logInfo = ['SERVER'=>$_SERVER,'AttemptedLogin'=>$request['EmailLogin']];
        $log = new Logger(json_encode($logInfo),4,$userID);
        $log->SaveLog();

        return redirect()
            ->back()
            ->withInput($request->only('EmailLogin', 'remember'))
            ->withErrors([
                //'EmailLogin' => $this->getFailedLoginMesssage(),
                'EmailLogin' => 'These credentials do not match our records.'
            ]);
    }

    public function getLogout()
    {
        $Redis = Redis::connection();
        $user = $this->auth->user();
        if(Session::get('_id'))
        {
            $Redis->del('laravel:' . Session::get('_id'));
            Session::flush();
        }
        $response = redirect('/');
        $response = CookieMonster::removeCookieFromResponse($response, 'user-token');
        $response = CookieMonster::removeCookieFromResponse($response, Config::get('session.cookie'));
        return $response;
    }

    public function linkSocialMedia($providerName,$email,$user)
    {
        $providerName = Crypt::decrypt($providerName);
        $email = Crypt::decrypt($email);

        $Provider = Providers::firstOrCreate(['Name' => $providerName]);
        // dd($Provider);
        $socialLink = SocialLogins::firstOrCreate(
            [
            'UserID' => $user->ID,
            'Provider' => $Provider->ID,
            'Email' =>  $email
            ]);
    }
}
