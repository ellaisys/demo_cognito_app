<?php

namespace App\Http\Controllers;

use Auth;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\User;

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


    /**
     * Action to register the user
     */
    public function actionRegister(Request $request)
    {
        return $this->register($request);
    } //Function ends

    public function create (array $data)
    {
        return User::create($data);
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

            if ($this->reset($request)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password updated successfully'
                ], 200);
            } else {
				return response()->json([
					'status' => 'error',
					'message' => 'Password update failed'
				], 400);
			} //End if
        } catch(Exception $e) {
            throw $e;
        } //Try-catch ends
    } //Function ends

} //Class ends
