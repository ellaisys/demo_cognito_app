<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

//use Illuminate\Foundation\Auth\AuthenticatesUsers; //Removed for AWS Cognito
use Ellaisys\Cognito\Auth\AuthenticatesUsers; //Added for AWS Cognito

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    /**
     * Authenticate User
     * 
     * @throws \HttpException
     * 
     * @return mixed
     */
    public function login(\Illuminate\Http\Request $request)
    {
        try {
            //Convert request to collection
            $collection = collect($request->all());

            //Authenticate with Cognito Package Trait (with 'web' as the auth guard)
            if ($response = $this->attemptLogin($collection, 'web')) {
                if ($response===true) {
                    $request->session()->regenerate();

                    return redirect(route('home'));
    
                       // ->intended('home');
                } else if ($response===false) {
                    return redirect()
                        ->back()
                        ->withInput($request->only('username', 'remember'))
                        ->withErrors([
                            'username' => 'Incorrect username and/or password !!',
                        ]);

                    //$this->incrementLoginAttempts($request);
                    //
                    //$this->sendFailedLoginResponse($collection, null);
                } else {
                    return $response;
                } //End if
            } //End if
        } catch(Exception $e) {
            Log::error($e->getMessage());
            return $response->back()->withInput($request);
        } //Try-catch ends

    } //Function ends
} //Class ends
