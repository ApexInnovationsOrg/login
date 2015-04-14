<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Helpers\Logger;
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
		//dd($decryptedString);
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
			if($expired)
			{
				return view('auth/linked',['successful' => false, 'provider' => $decryptedString->provider])->withErrors(array('Your link has expired'));
			}
			else
			{
				return view('auth/linked',['successful' => false, 'provider' => $decryptedString->provider])->withErrors(array('Accounts already linked'));
			}
		}
		
	}

	public function linkNow()
	{
			$jsonUser = (object)json_decode(Crypt::decrypt(Input::get('user')));
			$user = User::find($jsonUser->ID);
			$Provider = Providers::firstOrCreate(['Name' => Input::get('providerName')]);
			$socialLink = SocialLogins::firstOrCreate(['UserID' => $user->ID,'Provider' => $Provider->ID,'Email' => $user->Login]);
			
			Auth::login($user);
			$Redis = Redis::connection();
	        Session::put('userId', $user->ID);
	        Session::put('userID', $user->ID);
	        Session::put('userName', $user->FirstName.' '.$user->LastName);
	        Session::put('_id', Session::getId());
	        $Redis->set('User:' . $user->ID, Session::getId());
	        $user->LastLoginDate = date("Y-m-d H:i:s");
	        $user->save();
 			
 			$logInfo = ['SERVER'=>$_SERVER];
            $log = new Logger(json_encode($logInfo),5,$user->ID);
            $log->SaveLog();

	        $response = CookieMonster::addCookieToResponse(redirect(CookieMonster::redirectLocation()), 'user-token', $user->ID);
	        $response = CookieMonster::addCookieToResponse($response, Config::get('session.cookie'), Session::getId());
	        return $response;
	}

	public function register()
	{	
		if(Input::get('hasLicense') == "1")
		{
			return redirect('https://www.apexinnovations.com/CreateAccount.php?&Acct=1' . Input::get('Email'))->withInput();
		}
		else
		{
			$jsonAuth = (object)json_decode(Crypt::decrypt(Input::get('auth')));

			
			$Provider = Providers::firstOrCreate(['Name' => $jsonAuth->profile->providerName]);
			if(empty($jsonAuth->profile->name->givenName) || empty($lastName = $jsonAuth->profile->name->familyName))
			{

				$splitName = preg_split('/\s+/', $jsonAuth->profile->name->formatted);
				$firstName = $splitname[1];
				$lastName = $splitname[0];
			}	
			else
			{
				$firstName = $jsonAuth->profile->name->givenName;
				$lastName = $jsonAuth->profile->name->familyName;
			}

			$user = User::firstOrNew(['Login' => $jsonAuth->profile->email,'FirstName' => $firstName,'LastName' => $lastName]);

			if(!$user->ID)//this means that the user in fact does NOT exist, and needs to have the info.
			{
				$this->setDefaultUserInfo($user);
			}	

			$socialLink = SocialLogins::firstOrCreate(['UserID' => $user->ID,'Provider' => $Provider->ID,'Email' => $user->Login]);
			
			Auth::login($user);
			$Redis = Redis::connection();
	        Session::put('userId', $user->ID);
	        Session::put('userID', $user->ID);
	        Session::put('userName', $user->FirstName.' '.$user->LastName);
	        Session::put('_id', Session::getId());
	        $Redis->set('User:' . $user->ID, Session::getId());
	        $user->LastLoginDate = date("Y-m-d H:i:s");
	        $user->save();
 			
 			$logInfo = ['SERVER'=>$_SERVER];
            $log = new Logger(json_encode($logInfo),6,$user->ID);
            $log->SaveLog();

	        $response = CookieMonster::addCookieToResponse(redirect(CookieMonster::redirectLocation()), 'user-token', $user->ID);
	        $response = CookieMonster::addCookieToResponse($response, Config::get('session.cookie'), Session::getId());
	        return $response;
		}
	}


	private function setDefaultUserInfo($user)
	{
		$user->Password = bcrypt('36903b4db385551b6d114d659dc37d3');
		$user->Address = '3909 Ambassador Caffery Pkwy';
		$user->CreationDate = date("Y-m-d H:i:s");
		$user->Address2 = 'Bldg K';
		$user->City = 'Lafayette';
		$user->StateID = '27';
		$user->CountryID = '231';
		$user->DepartmentID = '584';
		$user->LMS = 'N';
		$user->Active = 'Y';
		$user->Beta = 'N';
		$user->ShowDemoReporting = 'N';
		$user->PasswordChangedByAdmin = 'N';
		$user->Locale = 'en-us';
		$user->oldUser = 'N';
		$user->save();
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
				        $user->LastLoginDate = date("Y-m-d H:i:s");
				        $user->save();

				        $logInfo = ['SERVER'=>$_SERVER];
			            $log = new Logger(json_encode($logInfo),6,$user->ID);
			            $log->SaveLog();

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
					return view('/auth/social',['verifiedEmail' => $authJSON->profile->verifiedEmail,'providerName' => $authJSON->profile->providerName, 'auth' => Crypt::encrypt(json_encode($authJSON))]);
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
		$emailEncrypted = null;
		$providerEncrypted = null;
		
		//if crypt will encrypt a null string with value. The result is that 'old' array from the redirect->back()->withInput() will by overridden by the encrypted null value.
		if(!empty(Input::get('email')))
		{	
			$emailEncrypted = Crypt::encrypt(Input::get('email'));
			$providerEncrypted = Crypt::encrypt(Input::get('providerName'));
		}

		$email = Input::get('email');
		$provider = Input::get('providerName');
		return view('auth/differentLogin',['email'=>$email,'provider'=>$provider,'emailName'=>$emailEncrypted,'providerName'=>$providerEncrypted]);
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
	public function verifyEmail(Request $request)
	{


  		$this->validate($request, [
            'email' => 'required|email', 
            'provider' => 'required',
        ]);

		$Login = Input::get('Login');
		$user = User::where('Login', '=', $Login)->first();
		$email = Input::get('email');
		$provider = Input::get('providerName');
		$emailName = Input::get('emailName');
		$providerName = Input::get('providerName');

		
	
		if(!empty($user))
		{
			// return redirect('auth/Social/email','302',array('Request Method' => 'POST'))->withInput(['user' => Crypt::encrypt($user),'email'=>$email,'provider'=>$provider]);

			return $this->sendAuthorizationEmail();
		}
		else
		{
			//dd(['email'=>$email,'provider'=>$provider]);
			//dd(Input::all());

			return view('auth/differentLogin',['email'=>$email,'provider'=>$provider,'emailName'=>$emailName,'providerName'=>$providerName])->withErrors(array('User does not exist'));
			//return redirect()->back()->withInput()->withErrors(array('User does not exist'));
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
