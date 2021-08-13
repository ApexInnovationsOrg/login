<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;

class UnfinishedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check())
        {   
            if(auth()->user()->DepartmentID == null || auth()->user()->CredentialID == null)
            {
                return $next($request);
            }
            else
            {
                return redirect(RouteServiceProvider::HOME);
            }
        }
        else
        {
            return redirect('/login');
        }
    }
}
