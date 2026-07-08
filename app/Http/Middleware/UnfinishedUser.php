<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnfinishedUser
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            if (auth()->user()->DepartmentID == null || auth()->user()->CredentialID == null) {
                return $next($request);
            } else {
                return redirect(RouteServiceProvider::HOME);
            }
        } else {
            return redirect('/login');
        }
    }
}
