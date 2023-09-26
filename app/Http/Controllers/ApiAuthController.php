<?php

namespace App\Http\Controllers;

use Auth;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Ellaisys\Cognito\AwsCognitoClaim;
use Ellaisys\Cognito\Auth\AuthenticatesUsers;
use Ellaisys\Cognito\Auth\ChangePasswords;
use Ellaisys\Cognito\Auth\RegistersUsers;
//use Ellaisys\Cognito\Auth\RegisterMFA;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as BaseController;

use Exception;
use Illuminate\Validation\ValidationException;
use Ellaisys\Cognito\Exceptions\AwsCognitoException;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiAuthController extends BaseController
{
    use AuthenticatesUsers;
    use ChangePasswords;
    use RegistersUsers;
    //use RegisterMFA;
    

    // public function register(Request $request)
    // {
    //     $this->validator($request->all())->validate();

    //     $attributes = [];

    //     $userFields = ['name', 'email'];

    //     foreach($userFields as $userField) {

    //         if ($request->$userField === null) {
    //             throw new \Exception("The configured user field $userField is not provided in the request.");
    //         }

    //         $attributes[$userField] = $request->$userField;
    //     }

    //     app()->make(CognitoClient::class)->register($request->email, $request->password, $attributes);

    //     event(new Registered($user = $this->create($request->all())));

    //     return $this->registered($request, $user)
    //         ?: redirect($this->redirectPath());
    // } //Function ends


    /**
     * Action to register the user
     */
    public function actionRegister(Request $request)
    {
        try {
            return $this->register($request);
        } catch(Exception $e) {
            if ($e instanceof ValidationException) {
                //json response with 422 error
                return Response::json($e->errors(), 422);
            } //End if
            throw $e;
        } //Try-catch ends
    } //Function ends


    /**
     * Login action for the API based approach.
     */
    public function actionLogin(Request $request)
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
    } //Function ends


    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function getRemoteUser()
    {
        try {
            $user =  auth()->guard('api')->user();
            $response = auth()->guard()->getRemoteUserData($user['email']);
        } catch (NoLocalUserException $e) {
            $response = $this->createLocalUser($credentials);
        } catch (Exception $e) {
            return $e;
        }

        return $response;
    } //Function ends


	/**
	 * Action to update the user password
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 */
    public function actionChangePassword(Request $request)
    {
		try
		{
            //Validate request
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password'  => 'required',
                'new_password' => 'required|confirmed',
            ]);
            $validator->validate();

            // Get Current User
            $userCurrent = auth()->guard('web')->user();

            if ($this->reset($request)) {
                return redirect(route('login'))->with('success', true);
            } else {
				return redirect()->back()
					->with('status', 'error')
					->with('message', 'Password updated failed');
			} //End if
        } catch(Exception $e) {
			$message = 'Error sending the reset mail.';
			if ($e instanceof ValidationException) {
                $message = $e->errors();
            } else if ($e instanceof CognitoIdentityProviderException) {
				$message = $e->getAwsErrorMessage();
			} else {
                //Do nothing
            } //End if

			return redirect()->back()
				->with('status', 'error')
				->with('message', $message);
        } //Try-catch ends
    } //Function ends

} //Class ends
