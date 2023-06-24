<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use Ellaisys\Cognito\Auth\ChangePasswords as CognitoChangePasswords; //Added for AWS Cognito

use Exception;
use Illuminate\Validation\ValidationException;
use Ellaisys\Cognito\Exceptions\AwsCognitoException;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

class ChangePasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use CognitoChangePasswords;

    /**
     * Where to redirect users when the intended url fails.
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
        $this->middleware('auth');
    }


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
                'password'  => 'string|min:8',
                'new_password' => 'required|confirmed|min:8',
            ]);
            $validator->validate();

            // Get Current User
            $userCurrent = auth()->guard('web')->user();

            if ($this->reset($request)) {
                auth()->guard()->logout(true);
                $request->session()->invalidate();

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
    }
}
