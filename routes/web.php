<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

/**
 * Routes added below to manage the AWS Cognito change in case you are
 * using Laravel Scafolling
 */

Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::get('/login/mfa', function () { return view('auth.login_mfa_code'); })->name('cognito.form.mfa.code');
Route::post('/login/mfa', [App\Http\Controllers\AuthController::class, 'webLoginMFA'])->name('cognito.form.mfa.code');
Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::get('/password/forgot', function () { return view('auth.passwords.email'); })->name('password.request');
Route::get('/password/reset', function () { return view('auth.passwords.reset'); })->name('cognito.form.reset.password.code');

Route::middleware('aws-cognito')->get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware('aws-cognito')->get('/password/change', function () { return view('auth.passwords.change'); })->name('cognito.form.change.password');
Route::middleware('aws-cognito')->post('/password/change', [App\Http\Controllers\Auth\ChangePasswordController::class, 'actionChangePassword'])->name('cognito.action.change.password');
Route::middleware('aws-cognito')->any('logout', function (\Illuminate\Http\Request $request) { 
    Auth::guard()->logout();
    return redirect('/');
})->name('logout');
Route::middleware('aws-cognito')->any('logout/forced', function (\Illuminate\Http\Request $request) { 
    Auth::guard()->logout(true);
    return redirect('/');
})->name('logout_forced');
