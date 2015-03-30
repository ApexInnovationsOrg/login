<?php namespace App\Auth\Passwords;

use Closure;
use App\User;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Auth\Passwords\PasswordBroker as BasePasswordBroker;

class PasswordBroker extends BasePasswordBroker implements PasswordBrokerContract
{
    public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null)
    {
        $view = $this->emailView;
        // edit whatever here
        return $this->mailer->send($view, compact('token', 'user'), function($m) use ($user, $token, $callback)
        {
            $m->to($user->getEmailForPasswordReset());

            if ( ! is_null($callback)) call_user_func($callback, $m, $user, $token);
        });
    }
    /**
	 * Get the user for the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Contracts\Auth\CanResetPassword
	 *
	 * @throws \UnexpectedValueException
	 */
	public function getUser(array $credentials)
	{
		$credentials = array_except($credentials, ['token']);
		//['Login'=>'eddie@apex']
		$user = User::where('Login', '=' , $credentials['Login'])->first();

		if ($user && ! $user instanceof CanResetPasswordContract)
		{
			throw new UnexpectedValueException("User must implement CanResetPassword interface.");
		}

		return $user;
	}

}