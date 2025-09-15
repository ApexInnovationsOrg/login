<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse;
use Inertia\Inertia;

class CustomLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        // Force full-page redirect (not SPA/XHR)
        return Inertia::location('https://www.apexinnovations.com/MyCurriculum.php');
    }
}