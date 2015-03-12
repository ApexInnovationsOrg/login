<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
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
            return redirect()->intended($this->redirectPath());
        } else {
            $user = User::where('Login', '=', Input::get('Login'))->first();

            if(isset($user)) {
                if($user->Password == md5("6#pR8@" . Input::get('Password'))) { // If their Password is still the MD5 mess

                    // If we want to switch people over to a better hash algorithm
                    // $user->Password = Hash::make(Input::get('Password')); // Convert to new format
                    // $user->save();

                    $this->auth->login($user);
                }
            }
        }

        return redirect($this->loginPath())
            ->withInput($request->only('Login', 'remember'))
            ->withErrors([
                'Login' => $this->getFailedLoginMesssage(),
            ]);
    }

}
