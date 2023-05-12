<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

use Ellaisys\Cognito\AwsCognitoClaim;
use Ellaisys\Cognito\Auth\AuthenticatesUsers;
use Ellaisys\Cognito\Auth\ChangePasswords;
use Ellaisys\Cognito\Auth\RegisterMFA;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as BaseController;

use Exception;
use Ellaisys\Cognito\Exceptions\AwsCognitoException;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WebMFAController extends BaseController
{
    use AuthenticatesUsers;
    use ChangePasswords;
    use RegisterMFA;

	/**
	 * Action to activate MFA for the 
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 */
    public function actionActivateMFA(Request $request)
    {
		try
		{
            $user = auth()->guard('web')->user();
            $response = $this->activateMFA();
            $userCognito = auth()->guard('web')->getRemoteUserData($user->email);

            //Return status to screen
            return back()
                ->with('user', $userCognito->toArray())
                ->with('actionActivateMFA', $response);

        } catch(Exception $e) {
			$message = 'Error activating the MFA.';
			if ($e instanceof ValidationException) {
                $message = $e->errors();
            } else if ($e instanceof CognitoIdentityProviderException) {
				$message = $e->getAwsErrorMessage();
			} else {
                //Do nothing
            } //End if

			throw $e;
        } //Try-catch ends
    } //Function ends


	/**
	 * Action to deactivate MFA for the 
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 */
    public function actionDeactivateMFA(Request $request)
    {
		try
		{
            $user = auth()->guard('web')->user();
            $response = $this->deactivateMFA();
            $userCognito = auth()->guard('web')->getRemoteUserData($user->email);

            //Return status to screen
            return back()
                ->with('user', $userCognito->toArray())
                ->with('actionDeactivateMFA', $response);
        } catch(Exception $e) {
			$message = 'Error activating the MFA.';
			if ($e instanceof ValidationException) {
                $message = $e->errors();
            } else if ($e instanceof CognitoIdentityProviderException) {
				$message = $e->getAwsErrorMessage();
			} else {
                //Do nothing
            } //End if

			throw $e;
        } //Try-catch ends
    } //Function ends


	/**
	 * Action to enable MFA for the user
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 */
    public function actionEnableMFA(Request $request)
    {
		try
		{
            $user = auth()->guard('web')->user();
            $response = $this->enableMFA('web', $user->email);
            $userCognito = auth()->guard('web')->getRemoteUserData($user->email);

            //Return status to screen
            return back()
                ->with('user', $userCognito->toArray())
                ->with('actionEnableMFA', [
                    'status' => $response['@metadata']['statusCode']==200
                ]);
        } catch(Exception $e) {
			$message = 'Error activating the MFA.';
			if ($e instanceof ValidationException) {
                $message = $e->errors();
            } else if ($e instanceof CognitoIdentityProviderException) {
				$message = $e->getAwsErrorMessage();
			} else {
                //Do nothing
            } //End if

			throw $e;
        } //Try-catch ends
    } //Function ends


	/**
	 * Action to disable MFA for the user
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 */
    public function actionDisableMFA(Request $request)
    {
		try
		{
            $user = auth()->guard('web')->user();
            $response = $this->disableMFA('web', $user->email);
            $userCognito = auth()->guard('web')->getRemoteUserData($user->email);

            //Return status to screen
            return back()
                ->with('user', $userCognito->toArray())
                ->with('actionDisableMFA', [
                    'status' => $response['@metadata']['statusCode']==200
                ]);
        } catch(Exception $e) {
			$message = 'Error activating the MFA.';
			if ($e instanceof ValidationException) {
                $message = $e->errors();
            } else if ($e instanceof CognitoIdentityProviderException) {
				$message = $e->getAwsErrorMessage();
			} else {
                //Do nothing
            } //End if

			throw $e;
        } //Try-catch ends
    } //Function ends


	/**
	 * Verify the MFA user code
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 */
    public function actionVerifyMFA(Request $request)
    {
		try
		{
            return $this->verifyMFA('web', $code);
        } catch(Exception $e) {
			$message = 'Error activating the MFA.';
			if ($e instanceof ValidationException) {
                $message = $e->errors();
            } else if ($e instanceof CognitoIdentityProviderException) {
				$message = $e->getAwsErrorMessage();
			} else {
                //Do nothing
            } //End if

			throw $e;
        } //Try-catch ends
    } //Function ends


    /**
     * Authenticate using the MFA code using the Web console
     */
    public function actionValidateMFA(Request $request)
    {
        try
        {
            //Create credentials object
            $collection = collect($request->all());

            $response = $this->attemptLoginMFA($request);

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
            } else {
                return $response;
            } //End if
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $response = $this->sendFailedLoginResponse($collection, $e);
            return $response->back()->withInput($request->only('username', 'remember'));
        } //try-catch ends
    } //Function ends

} //Class ends
