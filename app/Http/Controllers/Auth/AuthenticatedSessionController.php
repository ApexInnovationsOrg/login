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

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
