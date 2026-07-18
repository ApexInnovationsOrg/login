<?php

use App\Http\Controllers\Api\Admin\LookupController;
use App\Http\Controllers\Api\Admin\RoutingRuleController;
use App\Http\Controllers\Api\Admin\SamlClientController;
use App\Http\Controllers\Api\Admin\SsoGrantController;
use App\Http\Controllers\Api\Admin\SsoHandoffController;
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
    Route::get('/organizations', [LookupController::class, 'organizations'])->name('organizations.index');
    Route::get('/organizations/{organizationId}/departments', [LookupController::class, 'departments'])->whereNumber('organizationId')->name('organizations.departments');
    Route::get('/systems', [LookupController::class, 'systems'])->name('systems.index');
    Route::get('/saml-clients/{slug}/users', [LookupController::class, 'users'])->name('saml-clients.users');
    Route::get('/saml-clients', [SamlClientController::class, 'index'])->name('saml-clients.index');
    Route::get('/saml-clients/{slug}', [SamlClientController::class, 'show'])->name('saml-clients.show');
    Route::post('/saml-clients', [SamlClientController::class, 'store'])->name('saml-clients.store');
    Route::patch('/saml-clients/{slug}', [SamlClientController::class, 'update'])->name('saml-clients.update');
    Route::post('/saml-clients/{slug}/idp-metadata', [SamlClientController::class, 'idpMetadata'])->name('saml-clients.idp-metadata');
    Route::post('/saml-clients/{slug}/enable', [SamlClientController::class, 'enable'])->name('saml-clients.enable');
    Route::post('/saml-clients/{slug}/disable', [SamlClientController::class, 'disable'])->name('saml-clients.disable');
    Route::get('/saml-clients/{slug}/grants', [SsoGrantController::class, 'index'])->name('saml-clients.grants.index');
    Route::put('/saml-clients/{slug}/grants', [SsoGrantController::class, 'replace'])->name('saml-clients.grants.replace');
    Route::get('/saml-clients/{slug}/routing-rules', [RoutingRuleController::class, 'show'])->name('saml-clients.routing-rules.show');
    Route::put('/saml-clients/{slug}/routing-rules', [RoutingRuleController::class, 'replace'])->name('saml-clients.routing-rules.replace');
    Route::get('/saml-clients/{slug}/routable-organizations', [RoutingRuleController::class, 'routableOrganizations'])->name('saml-clients.routable-organizations');
    Route::post('/sso-handoff/redeem', [SsoHandoffController::class, 'redeem'])->name('sso-handoff.redeem');
});
