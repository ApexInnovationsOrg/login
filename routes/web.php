<?php

use Illuminate\Foundation\Application;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Auth\AdminPasswordReset;
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
Route::get('/test',function(){
    $testUser = User::find(152002);
    // dd(get_class(Auth::getFacadeRoot()));
    // Auth::loginUsingId(152002);
    dd($testUser->getPasswordRequirements());
    // dd(Auth::user());
    // return redirect('/dashboard');
    // dd(Redis::get("laravel:70b844450735dbe01e48632780253fb7"));
});
Route::get('/dashboard', function () {
    // dd('dashboard');
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');


require __DIR__.'/auth.php';
