<?php namespace App\Auth;

use App\User;

use App\Helpers\CookieMonster;
use App\Helpers\SessionHelper;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;


use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResetsPasswords {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * The password broker implementation.
	 *
	 * @var PasswordBroker
	 */
	protected $passwords;

	/**
	 * Display the form to request a password reset link.
	 *
	 * @return Response
	 */
	public function getEmail()
	{
		return view('auth.password');
	}

	/**
	 * Send a reset link to the given user.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function postEmail(Request $request)
	{
		
		$this->validate($request, ['Login' => 'required|email']);
		$response = $this->passwords->sendResetLink($request->only('Login'), function($m)
		{
			$m->subject($this->getEmailSubject());
		});
		switch ($response)
		{
			case PasswordBroker::RESET_LINK_SENT:

				return view('auth.password')->with('status', trans($response));

			case PasswordBroker::INVALID_USER:
				return view('auth.password')->withErrors(['Login' => trans($response)]);
			default:

				return view('auth.password')->withErrors(['Login' => 'oh no!']);
		}
	}

	/**
	 * Get the e-mail subject line to be used for the reset link Login.
	 *
	 * @return string
	 */
	protected function getEmailSubject()
	{
		return isset($this->subject) ? $this->subject : 'Your Password Reset Link';
	}

	/**
	 * Display the password reset view for the given token.
	 *
	 * @param  string  $token
	 * @return Response
	 */
	public function getReset($token = null)
	{
		if (is_null($token))
		{
			//throw new NotFoundHttpException;
			return view('auth.login')->withErrors(['Login' => 'No token specified']);;
		}

		return view('auth.reset')->with('token', $token);
	}

	/**
	 * Reset the given user's password.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function postReset(Request $request)
	{

		if(empty(Input::get('token')))
		{
			$this->validate($request, [
				'Login' => 'required|email',
				'oldPassword' => 'required',
				'Password' => 'required|confirmed|min:6',
			]);

			$credentials = $request->only(
				'Login', 'oldPassword', 'Password', 'Password_confirmation'
			);

			$response = $this->passwords->reset($credentials, function($user, $password)
			{
				return SessionHelper::authenticateUserSession($user->ID);
			});
			
		}
		else 
		{


			$this->validate($request, [
				'token' => 'required',
				'Login' => 'required|email',
				'Password' => 'required|confirmed|min:6',
			]);

			$credentials = $request->only(
				'Login', 'Password', 'Password_confirmation', 'token'
			);

			$response = $this->passwords->reset($credentials, function($user, $password)
			{
				$user->Password = bcrypt($password);
				unset($user->email);
				$user->PasswordLastChanged = date("Y-m-d H:i:s");
				$user->save();

				return SessionHelper::authenticateUserSession($user->ID);
			});
		}


		
		switch ($response)
		{
			case PasswordBroker::PASSWORD_RESET:
				return redirect(CookieMonster::redirectLocation());

			default:
				// return view('auth.reset')
				// 			->withInput($request->only('Login'))
				// 			->withErrors(['Login' => trans($response)]);
				return redirect()->back()
							->withInput($request->only('Login'))
							->withErrors(['Login' => trans($response)]);
		}
	}

	/**
	 * Get the post register / login redirect path.
	 *
	 * @return string
	 */
	public function redirectPath()
	{
		if (property_exists($this, 'redirectPath'))
		{
			return $this->redirectPath;
		}

		return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
	}

}
