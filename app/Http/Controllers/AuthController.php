<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

use Ellaisys\Cognito\AwsCognitoClaim;
use Ellaisys\Cognito\Auth\AuthenticatesUsers;
use Ellaisys\Cognito\Auth\ChangePasswords;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Routing\Controller as BaseController;

use Exception;
use Ellaisys\Cognito\Exceptions\AwsCognitoException;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends BaseController
{
    use AuthenticatesUsers;
    use ChangePasswords;

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $attributes = [];

        $userFields = ['name', 'email'];

        foreach($userFields as $userField) {

            if ($request->$userField === null) {
                throw new \Exception("The configured user field $userField is not provided in the request.");
            }

            $attributes[$userField] = $request->$userField;
        }

        app()->make(CognitoClient::class)->register($request->email, $request->password, $attributes);

        event(new Registered($user = $this->create($request->all())));

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }


    public function login(Request $request)
    {
        //Create credentials object
        $collection = collect($request->all());

        

        if ($claim = $this->attemptLogin($collection, 'api', 'username', 'password', true)) {

            if ($claim instanceof AwsCognitoClaim) {
                return $claim->getData();
            } else {
                return $claim;
            } //End if
        }
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function getRemoteUser($username)
    {
        try {
            $response = Auth::guard()->user();
        } catch (NoLocalUserException $e) {
            dd($e);
            $response = $this->createLocalUser($credentials);
        }

        return $response;
    }


    public function webLogin(Request $request)
    {
        try
        {
            //Create credentials object
            $collection = collect($request->all());

            $response = $this->attemptLogin($collection, 'web');

            if ($response===true) {
                $request->session()->regenerate();

                return redirect()
                    ->intended('home');
            } else if ($response===false) {
                return redirect()
                    ->back()
                    ->withInput($request->only('username', 'remember'))
                    ->withErrors([
                        'username' => 'Incorrect username and/or password !!',
                    ]);
            } else {
                return $response;
            } //End if
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $response = $this->sendFailedLoginResponse($collection, $e);
            return $response->back()->withInput($request->only('username', 'remember'));
        } //try-catch ends
    }


    public function actionChangePassword(Request $request)
    {
        if ($this->reset($request)) {
            return redirect(route('home'))->with('success', true);
        };
    }

}