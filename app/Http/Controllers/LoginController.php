<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\CookieMonster;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Input;
use App\Helpers\SessionHelper;


class LoginController extends Controller {

        /**
         * Display a listing of the resource.
         *
         * @return Response
         */
        public function index()
        {

                $args = func_get_args();

                $user = 'stuff';

                return redirect('//www.apexwebtest.com/doLogon.php');
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return Response
         */
        public function login()
        {

                if(Auth::check())
                {
                        return redirect(url(CookieMonster::redirectLocation()));
                }
                else
                {
                        return view('auth/login');
                }
        }
        public function getLogout()
        {
            $Redis = Redis::connection();

            if(Session::get('_id'))
            {
                $Redis->del('laravel:' . Session::get('_id'));
                Session::flush();
            }
            session_start();

            session_unset();

            session_destroy();
            $response = redirect('/');
            $response = CookieMonster::removeCookieFromResponse($response, 'user-token');
            return $response;
        }
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
                if(Input::get('providerName') != null)
                {
                    $this->linkSocialMedia(Input::get('providerName'),Input::get('emailName'),$user);
                }
                return SessionHelper::authenticateUserSession($user->ID);
            } else {
                $user = User::where('Login', '=', Input::get('EmailLogin'))->first();

                if(isset($user)) {
                    if($user->Password == md5("6#pR8@" . Input::get('Password')))
                    { // If their Password is still the MD5 mess


                        $logInfo = ['SERVER'=>$_SERVER,'Password'=>'md5'];
                        if(Input::get('providerName') != null)
                        {
                             $this->linkSocialMedia(Input::get('providerName'),Input::get('emailName'),$user);
                        }

                        return SessionHelper::authenticateUserSession($user->ID);
                    }
                }
            }
            $userID = isset($user) ? $user->ID : 0;
            $logInfo = ['SERVER'=>$_SERVER,'AttemptedLogin'=>$request['EmailLogin']];



            return redirect()
                ->back()
                ->withInput($request->only('EmailLogin', 'remember','emailName','providerName','email','provider'))
                ->withErrors([
                    //'EmailLogin' => $this->getFailedLoginMesssage(),
                    'EmailLogin' => 'These credentials do not match our records.'
                ]);
        }
        /**
         * Store a newly created resource in storage.
         *
         * @return Response
         */
        public function healthcheck()
        {
                return "<marquee>Hello!</marquee>";
        }

        /**
         * Display the specified resource.
         *
         * @param  int  $id
         * @return Response
         */
        public function show($id)
        {
                //
        }

        /**
         * Show the form for editing the specified resource.
         *
         * @param  int  $id
         * @return Response
         */
        public function edit($id)
        {
                //
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

        /**
         * Remove the specified resource from storage.
         *
         * @param  int  $id
         * @return Response
         */
        public function destroy($id)
        {
                //
        }

}