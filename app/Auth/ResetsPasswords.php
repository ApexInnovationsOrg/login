<?php namespace App\Auth;

use App\User;
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
			$user->password = bcrypt($password);
			// unset($user->email);
			dd($user);
			$user->save();

			$this->auth->login($user);
		});
		
		switch ($response)
		{
			case PasswordBroker::PASSWORD_RESET:
				return redirect($this->redirectPath());

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
