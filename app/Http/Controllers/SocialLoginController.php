<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Helpers\CookieMonster;

use App\Providers;
use App\SocialLogins;
use App\User;

use Crypt;
use Mail;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;

class SocialLoginController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return redirect('/auth/login')->withErrors(array("Failure logging in. Please try again."));
		//return 'home';
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function createLink()
	{
		$encryptedString = Input::get('data');
		$decryptedString = (object)Crypt::decrypt($encryptedString);
		//{"UserID":"362645","provider":"Yahoo!","email":"eddimull@yahoo.com"}
		$Provider = Providers::firstOrCreate(['Name' => $decryptedString->provider]);
		$socialLink = SocialLogins::where('UserID', '=', $decryptedString->UserID)->where('Provider', '=', $Provider->ID)->first();
		$expired = strtotime($decryptedString->datetime) < strtotime("-30 minutes") ? true : false;
		//dd($expired);
		if(empty($socialLink) && !$expired)
		{
			$socialLink = new SocialLogins;
			$socialLink->UserID = $decryptedString->UserID;
			$socialLink->Provider = $Provider->ID;
			$socialLink->Email = $decryptedString->email;
			$socialLink->save();
			return view('auth/linked',['successful'=>true,'provider'=>$decryptedString->provider]);
		}
		else
		{

			return view('auth/linked',['successful' => false, 'provider' => $decryptedString->provider])->withErrors(array('Already linked or link expired'));
		}
		
	}
	public function register()
	{	
		if(Input::get('hasLicense') == "1")
		{
			return redirect('https://www.apexinnovations.com/CreateAccount.php?&Acct=1' . Input::get('Email'))->withInput();
		}
		else
		{
			return redirect('https://www.apexinnovations.com/CreateAccount.php?NIH=1&Acct=1&Email=' . Input::get('Email'));
		}
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function sendAuthorizationEmail()
	{
		if(Input::get('user'))
		{
			//coming from auth/social, but the user already has an apex account
			$user = (object)json_decode(Crypt::decrypt(Input::get('user')));
		}
		else
		{
			//apex account doesn't exist, so the user is set from the verifyEmail() method
			$Login = Input::get('Login');
			$user = User::where('Login', '=', $Login)->first();
		}

		$name = $user->FirstName . ' ' . $user->LastName;
		$userEmail = $user->Login;
		$datetime = date('Y-m-d H:i:s');
		$email = Input::get('email');
		$provider = Input::get('providerName');

		$encrypted = Crypt::encrypt(['UserID' => $user->ID, 'provider' => $provider, 'email' => $email, 'datetime' => $datetime]);
		Mail::send('emails.socialMediaLink', array('encryptedLink' => $encrypted, 'name' => $name, 'provider' => $provider), function($message) use ($userEmail,$provider,$datetime) 
		{
		    $message->to($userEmail)->subject('Link your ' . $provider . ' account');
		});


		return view('auth/sentEmail',['verifiedEmail' => $userEmail, 'user' => Crypt::encrypt(json_encode($user)), 'providerName' => $provider]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		$token = Input::get('token');
		if(!empty($token))
		{
			$janrainApiKey = "ac6cd0fbe0e3710586b35343813023bf1ba570b6";
			$engageUrl = 'https://rpxnow.com/api/v2/auth_info';
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $engageUrl);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, array('token' => $token, 'apiKey' => $janrainApiKey));
			$authInfo = curl_exec($curl);
			$authJSON = (object)json_decode($authInfo);
			curl_close($curl);
			if($authJSON->stat == "ok")
			{

				//this is completely undry code. yaaaay last minute fixes. 

				if(empty($authJSON->profile->verifiedEmail))
				{
					$authJSON->profile->verifiedEmail = $authJSON->profile->email;
				}

				$provider = Providers::firstOrCreate(['Name' => $authJSON->profile->providerName]);
				$socialLinkCheck = SocialLogins::where('Email', '=', $authJSON->profile->verifiedEmail)
				->where('Provider', '=', $provider->ID)
				->first();
				if(!empty($socialLinkCheck))
				{
					$user = User::where('ID', '=', $socialLinkCheck->UserID)->first();
				}
				else 
				{
					$user = User::where('Login', '=', $authJSON->profile->verifiedEmail)->first();
				}


				
				if(!empty($user))
				{
					$provider = Providers::firstOrCreate(['Name' => $authJSON->profile->providerName]);
					$socialLink = SocialLogins::where('Email', '=', $authJSON->profile->verifiedEmail)->where('Provider', '=', $provider->ID)->first();

					if(!empty($socialLink))
					{
						Auth::login($user);
						$Redis = Redis::connection();
				        Session::put('userId', $user->ID);
				        Session::put('userID', $user->ID);
				        Session::put('userName', $user->FirstName.' '.$user->LastName);
				        Session::put('_id', Session::getId());
				        $Redis->set('User:' . $user->ID, Session::getId());
				        
				        $response = CookieMonster::addCookieToResponse(redirect(CookieMonster::redirectLocation()), 'user-token', $user->ID);
				        $response = CookieMonster::addCookieToResponse($response, Config::get('session.cookie'), Session::getId());
				        return $response;
					}
					else
					{
						//suggest account link
						return view('/auth/social',['verifiedEmail' => $authJSON->profile->verifiedEmail, 'user' => Crypt::encrypt($user), 'providerName' => $authJSON->profile->providerName]);
					}
				}
				else
				{
					//dd('link or create account. no suggestion');
					return view('/auth/social',['verifiedEmail' => $authJSON->profile->verifiedEmail,'providerName' => $authJSON->profile->providerName]);
				}
			}
			else
			{
				return redirect('/auth/login')->withErrors($authJSON->err->msg);
			}
		} 	
		else 
		{
			
			return redirect('/auth/login')->withErrors("Failure logging in. Please try again.");
		}

	}

	public function linkDifferentAccount()
	{
		$email = Input::get('email');
		$provider = Input::get('providerName');
		return view('auth/differentLogin',['email'=>$email,'provider'=>$provider]);
	}
	public function landing(Request $request)
	{
		return view('/auth/registerLanding');
	}
	public function licenseKey(Request $request)
	{
		return view('/auth/register2');
	}
	public function userInfo(Request $request)
	{
		return view('/auth/register3');
	}
	public function userLocationInfo(Request $request)
	{
		return view('/auth/register4');
	}
	public function departmentAndRoles(Request $request)
	{
		return view('/auth/register5');
	}
	public function verifyEmail()
	{

		$Login = Input::get('Login');
		$user = User::where('Login', '=', $Login)->first();
		$email = Input::get('email');
		$provider = Input::get('providerName');

		if(!empty($user))
		{
			// return redirect('auth/Social/email','302',array('Request Method' => 'POST'))->withInput(['user' => Crypt::encrypt($user),'email'=>$email,'provider'=>$provider]);

			return $this->sendAuthorizationEmail();
		}
		else
		{
			return view('auth/differentLogin',['email'=>$email,'provider'=>$provider])->withErrors(array('User does not exist'));
			//return redirect()->back()->withErrors(array('User does not exist'));
		}
	}
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


}
