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
    Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);


    Route::group(['middleware' => 'aws-cognito'], function() {
        Route::get('profile', [App\Http\Controllers\AuthController::class, 'getRemoteUser']);
        Route::put('logout', function (\Illuminate\Http\Request $request) {
            Auth::guard('api')->logout();
        });        
        Route::post('refresh-token', [App\Http\Controllers\ResetController::class, 'actionRefreshToken']);
    });
});


