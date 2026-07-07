<?php

use App\Http\Controllers\Api\Admin\SamlClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')->middleware('admin.api')->name('admin.')->group(function () {
    Route::get('/saml-clients', [SamlClientController::class, 'index'])->name('saml-clients.index');
    Route::get('/saml-clients/{slug}', [SamlClientController::class, 'show'])->name('saml-clients.show');
});
