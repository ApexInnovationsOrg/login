<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SamlController;
use App\Http\Controllers\Auth\SsoLookupController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

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
    ->middleware('guest');

Route::get('/dashboard', function () {
    $url = config('app.mycurriculum_url');

    if (Session::has('SAML')) { // SAML only likes 302s. You can't do external 302s with Inertia....sooo you get both.
        return redirect()->away($url);
    }

    return Inertia::location($url);

})->middleware(['auth'])->name('dashboard');

Route::post('/sso/lookup', SsoLookupController::class)
    ->middleware(['guest', 'throttle:10,1'])
    ->name('sso.lookup');
Route::get('/saml/{slug}/login', [SamlController::class, 'login'])->name('saml.login');

Route::post('/saml/{slug}/acs', [SamlController::class, 'acs'])->name('saml.acs');
Route::get('/saml/{slug}/metadata', [SamlController::class, 'metadata'])->name('saml.metadata');

require __DIR__.'/auth.php';
