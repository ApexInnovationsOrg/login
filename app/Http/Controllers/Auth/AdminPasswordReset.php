<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminPasswordReset extends Controller
{
    /**
     * Show the confirm password view.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return Inertia::render('Auth/AdminResetPassword',['Login'=>Auth::user()->Login]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'Login' => 'required|email',
            'password' => $user->getPasswordRequirements(),
        ]);


        $user = Auth::user();   
        $user->forceFill([
            'Password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();
        
        $user->PasswordChangedByAdmin = 'N';
        $user->save();

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.

        return redirect(RouteServiceProvider::HOME);
        

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
        
    }
}
