<?php

use Illuminate\Foundation\Application;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Auth\AdminPasswordReset;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [AuthenticatedSessionController::class, 'create'])
                ->middleware('guest')
                ->name('login');

Route::get('/dashboard', function () {
    $url = 'https://www.apexinnovations.com/MyCurriculum.php';
    
    if(Session::has('SAML')) //SAML only likes 302s. You can't do external 302s with Inertia....sooo you get both. 
    {
        return redirect()->away($url);
    }
    return Inertia::location($url);
    
})->middleware(['auth'])->name('dashboard');


require __DIR__.'/auth.php';
