<?php namespace App\Auth\Passwords;

use Closure;
use App\User;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Passwords\PasswordBroker as BasePasswordBroker;

class PasswordBroker extends BasePasswordBroker implements PasswordBrokerContract
{
    public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null)
    {
        //$view = $this->emailView;
        $view = ['emails.password','emails.passwordText'];

        // edit whatever here
        return $this->mailer->send($view, compact('token', 'user'), function($m) use ($user, $token, $callback)
        {
            $m->to($user->getEmailForPasswordReset());

            if ( ! is_null($callback)) call_user_func($callback, $m, $user, $token);
        });
    }

	/**
	 * Reset the password for the given token.
	 *
	 * @param  array     $credentials
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public function reset(array $credentials, Closure $callback)
	{
		
		// If the responses from the validate method is not a user instance, we will
		// assume that it is a redirect and simply return it from this method and
		// the user is properly redirected having an error message on the post.
		$user = $this->validateReset($credentials);

		if ( ! $user instanceof CanResetPasswordContract)
		{

			return $user;
		}
		$pass = $credentials['Password'];

		// Once we have called this callback, we will remove this token row from the
		// table and return the response from this callback so the user gets sent
		// to the destination given by the developers from the callback return.
		
		call_user_func($callback, $user, $pass);
		if(isset($credentials['token']))
		{
			$this->tokens->delete($credentials['token']);
		}

		return PasswordBrokerContract::PASSWORD_RESET;
		

	}

	/**
	 * Validate a password reset for the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Contracts\Auth\CanResetPassword
	 */
	protected function validateReset(array $credentials)
	{

		if (is_null($user = $this->getUser($credentials)))
		{
			return PasswordBrokerContract::INVALID_USER;
		}

		if ( ! $this->validateNewPassword($credentials))
		{
			return PasswordBrokerContract::INVALID_PASSWORD;
		}

		if(isset($credentials['token']))
		{
			if ( ! $this->tokens->exists($user, $credentials['token']) )
			{
				return PasswordBrokerContract::INVALID_TOKEN;
			}
		}
		if(isset($credentials['oldPassword']))
		{	
			if (! (Hash::check($credentials['oldPassword'],$user->Password)) && ! ($user->Password == md5("6#pR8@" . $credentials['oldPassword'])))
	        { 
				return PasswordBrokerContract::INVALID_PASSWORD;
	        }
		}

		return $user;
	}

	/**
	 * Set a custom password validator.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function validator(Closure $callback)
	{
		$this->passwordValidator = $callback;
	}
	/**
	 * Determine if the passwords are valid for the request.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	protected function validatePasswordWithDefaults(array $credentials)
	{
		list($password, $confirm) = [
			$credentials['Password'], $credentials['Password_confirmation']
		];

		return $password === $confirm && mb_strlen($password) >= 6;
	}

	/**
	 * Determine if the passwords match for the request.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateNewPassword(array $credentials)
	{
		list($password, $confirm) = [
			$credentials['Password'], $credentials['Password_confirmation']
		];

		if (isset($this->passwordValidator))
		{
			return call_user_func(
				$this->passwordValidator, $credentials) && $password === $confirm
			;
		}

		return $this->validatePasswordWithDefaults($credentials);
	}

	// public function validateOldPassword($credentials, $user)
	// {
 //        if (Hash::check($credentials['oldPassword'],$user->Password))
 //        { 
 //        	$user->Password = bcrypt($credentials['Password']);
	// 		$user->PasswordLastChanged = date("Y-m-d H:i:s");
	// 		$user->PasswordChangedByAdmin = 'N';
	// 		unset($user->email);
	// 		$user->save();
 //            return PasswordBrokerContract::PASSWORD_RESET;
 //        } 
 //        else 
 //        {	
 //            if($user->Password == md5("6#pR8@" . $credentials['oldPassword'])) 
 //            {
	//         	$user->Password = bcrypt($credentials['Password']);
	// 			$user->PasswordLastChanged = date("Y-m-d H:i:s");
	// 			$user->PasswordChangedByAdmin = 'N';
	// 			unset($user->email);
	// 			$user->save();
 //               	return PasswordBrokerContract::PASSWORD_RESET;
 //            }
	// 		return PasswordBrokerContract::INVALID_PASSWORD;
 //        }
		   
	// }
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
		$user = User::where('Login', '=' , trim($credentials['Login']))->first();
		//20150330EM had to do a bit of hotwiring to make the CanResetPasswordContract happy. At the time of writing this, there's not a lot of documentation on altering a contract for Laravel 5, and this PHP is simply beyond my level of comprehension. 
		if($user)
		{
			$user->email = $user->Login;
		}
		
		if ($user && ! $user instanceof CanResetPasswordContract)
		{
			throw new UnexpectedValueException("User must implement CanResetPassword interface.");
		}

		return $user;
	}

}