<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use Monolog\Level; // The StreamHandler sends log messages to a file on your disk
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);


        $translatedCreds = [
            'Login'=>$request->only('email')['email'],
            'password'=>$request->only('password')['password']
        ];

        if (Auth::attempt($translatedCreds)) {
            $user = Auth::user();
            
            if(Auth::user()->Disabled == 'Y') {
                Auth::logout();
                return redirect('login')->withErrors(['Your account has been disabled']);
            }
          
            $user->LastLoginDate = Carbon::now();
            $user->save();
            
            $request->session()->put('userId',$user->ID);
            $request->session()->put('userID',$user->ID);
            $request->session()->put('userName',$user->FirstName . ' ' . $user->LastName);
            $request->session()->put('Username',$user->FirstName . ' ' . $user->LastName);

            if($user->PasswordChangedByAdmin == 'Y')
            {
                return redirect()->intended('reset-made-password');
            }
            $request->session()->regenerate();

            if(isset($_COOKIE['ApexInnovations'])){
                $value = $_COOKIE['ApexInnovations'];
                $cookieVal = "Cookie is set: $value";
            }else{
                $cookieVal = "Cookie not set";
            }

            $log = new Logger('custom');
            $formatter = new JsonFormatter();
            $log->pushHandler((new StreamHandler("php://stdout", Logger::DEBUG))->setFormatter($formatter));

            $log->info('Session Started',["Cookie"=>$cookieVal,"IP"=>$this->getClientIP(),"Page"=>"Login"]);

            return Inertia::location('https://www.apexinnovations.com/MyCurriculum.php');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);

    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->pull('userId');
        $request->session()->pull('userID');
        $request->session()->pull('userName');
        $request->session()->pull('Username');
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

	private function getClientIP() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// In case of multiple IPs, take the first one
			return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
		}

		return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
	}
}
