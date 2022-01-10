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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::post('user/register', 'AuthController@register');
Route::post('user/login', 'AuthController@login');

Route::middleware('aws-cognito')->get('user', function (Request $request) {
    return Auth::guard('api')->user();
});
Route::middleware('aws-cognito')->post('user/logout', function (Request $request) {
    return Auth::guard('api')->logout();
});
Route::middleware('aws-cognito')->post('user/logout/all', function (Request $request) {
    return Auth::guard('api')->logoutOtherDevices($password);
});
