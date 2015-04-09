<?php namespace App\Http\Controllers\Auth;

use App\Helpers\CookieMonster;
use App\Helpers\Logger;
use App\Helpers\SessionManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
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
            $this->auth->login($user);
            $user = $this->auth->user();
            $logInfo = ['SERVER'=>$_SERVER,'Password'=>'bcrypt'];
            $log = new Logger(json_encode($logInfo),1,$user->ID);
            $log->SaveLog();
            return $this->authenticateUserSession($user->ID);
        } else {
            $user = User::where('Login', '=', Input::get('EmailLogin'))->first();

            if(isset($user)) {
                if($user->Password == md5("6#pR8@" . Input::get('Password'))) { // If their Password is still the MD5 mess

                    $this->auth->login($user);
                    $user = $this->auth->user();
                    $logInfo = ['SERVER'=>$_SERVER,'Password'=>'md5'];
                    $log = new Logger(json_encode($logInfo),1,$user->ID);
                    $log->SaveLog();
                    return $this->authenticateUserSession($user->ID);
                }
                // return redirect()->intended($this->redirectPath());
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
                'EmailLogin' => $this->getFailedLoginMesssage(),
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


    public function authenticateUserSession($userId) {
        $user = $this->auth->user();
        if($user->PasswordChangedByAdmin)
        {
            return view('reset/password',['Login' => $user->Login]);
        }
        else
        {
            $Redis = Redis::connection();
            Session::put('userId', $userId);
            // bad naming convention that continues to get carried over.
            Session::put('userID', $userId);
            Session::put('userName', $user->FirstName.' '.$user->LastName);
            Session::put('Username', $user->FirstName.' '.$user->LastName);
            Session::put('_id', Session::getId());
            $Redis->set('User:' . $userId, Session::getId());
            $user->LastLoginDate = date("Y-m-d H:i:s");
            $user->save();
            //Log::info('authenticateUserSession: '.print_r(['session'=>Session::getId()]));
            //$response = CookieMonster::addCookieToResponse(redirect()->intended($this->redirectPath()), 'user-token', $userId);
            $response = CookieMonster::addCookieToResponse(redirect()->intended(CookieMonster::redirectLocation()), 'user-token', $userId);
            $response = CookieMonster::addCookieToResponse($response, Config::get('session.cookie'), Session::getId());
            return $response;
        }
    }

    public function socialAuthentication()
    {

        // if(isset($_POST['token']))
        // {
        //     $token = $_POST['token'];
        //     $janrainApiKey = "ac6cd0fbe0e3710586b35343813023bf1ba570b6";
        //     $engageUrl = 'https://rpxnow.com/api/v2/auth_info';
        //     $curl = curl_init();
        //     curl_setopt($curl, CURLOPT_URL, $engageUrl);
        //     curl_setopt($curl, CURLOPT_POST, true);
        //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($curl, CURLOPT_POSTFIELDS, array('token' => $token, 'apiKey' => $janrainApiKey));
        //     $authInfo = curl_exec($curl);
        //     curl_close($curl);
        // }
    }

}
