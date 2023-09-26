<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('user')->group(function () {
    Route::post('login', [App\Http\Controllers\ApiAuthController::class, 'actionLogin']);
    Route::post('register', [App\Http\Controllers\ApiAuthController::class, 'actionRegister']);
    Route::post('/login/mfa', [App\Http\Controllers\ApiMFAController::class, 'actionValidateMFA']);

    Route::group(['middleware' => 'aws-cognito'], function() {
        Route::get('profile', [App\Http\Controllers\AuthController::class, 'getRemoteUser']);
        Route::post('mfa/enable', [App\Http\Controllers\ApiMFAController::class, 'actionApiEnableMFA']);
        Route::post('mfa/disable', [App\Http\Controllers\ApiMFAController::class, 'actionApiDisableMFA']);
        Route::get('mfa/activate', [App\Http\Controllers\ApiMFAController::class, 'actionApiActivateMFA']);
        Route::post('mfa/activate/{code}', [App\Http\Controllers\ApiMFAController::class, 'actionApiVerifyMFA']);
        Route::post('mfa/deactivate', [App\Http\Controllers\ApiMFAController::class, 'actionApiDeactivateMFA']);
        Route::put('logout', function (\Illuminate\Http\Request $request) {
            Auth::guard('api')->logout();
        });
        Route::put('logout/forced', function (\Illuminate\Http\Request $request) {
            Auth::guard('api')->logout(true);
        });
        Route::post('refresh-token', [App\Http\Controllers\ResetController::class, 'actionRefreshToken']);
    });
});


