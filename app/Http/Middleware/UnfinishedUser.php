<?php

namespace App\Http\Middleware;

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
                return redirect()->away(config('app.mycurriculum_url'));
            }
        } else {
            return redirect('/login');
        }
    }
}
