<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
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

require __DIR__.'/auth.php';
