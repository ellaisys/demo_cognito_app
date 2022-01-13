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

Route::get('/', function () { return view('welcome'); })->name('welcome');
Route::middleware('auth')->get('home', function () { return view('home'); })->name('home');

Route::get('login', function () { return view('auth.login'); })->name('login');
Route::post('login', 'AuthController@webLogin');
Route::get('register', function () { return view('auth.register'); })->name('register');
Route::post('register', 'User\UserController@webRegister')->name('register.form.action');

Route::get('forgot', function () { return view('auth.passwords.email'); })->name('cognito.form.reset.password');
Route::post('reset', 'User\UserController@sendPasswordResetEmail')->name('cognito.action.reset.password');
Route::get('reset/code', function () { return view('auth.passwords.reset'); })->name('cognito.form.reset.password.code');
Route::post('reset/code', 'User\UserController@actionResetPasswordCode')->name('cognito.action.reset.password.code');

Route::middleware('auth')->get('user/changepass', function () { return view('auth.passwords.confirm'); })->name('cognito.form.change.password');
Route::middleware('auth')->post('user/changepass', 'AuthController@actionChangePassword')->name('cognito.action.change.password');

Route::middleware('auth')->any('logout', function () { 
    Auth::guard()->logout();
    return redirect('/');
})->name('logout');

/**
 * Disable scaffolding routes
 */
//Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
