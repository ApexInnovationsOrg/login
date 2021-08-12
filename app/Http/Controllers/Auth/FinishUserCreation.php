<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Credential;
use App\Models\ProfessionalRole;
use Inertia\Inertia;

class FinishUserCreation extends Controller
{
    public function show()
    {
        $credentials = Credential::all();
        $roles = ProfessionalRole::all();
        return Inertia::render('Auth/FinishUser', [
            'credentials'=>$credentials,
            'roles'=>$roles
        ]);
    }

    public function store(Request $request)
    {
        dd('store the user details');
    }
}
