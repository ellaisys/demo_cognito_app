<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

use Ellaisys\Cognito\AwsCognitoClaim;
use Ellaisys\Cognito\Auth\RefreshToken;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as BaseController;

use Exception;
use Ellaisys\Cognito\Exceptions\AwsCognitoException;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResetController extends BaseController
{
    use RefreshToken;


    /**
     * 
     */
    public function actionRefreshToken(Request $request)
    {
        try {
            //Call the refresh token API
            $response = $this->refresh($request);
        } catch (Exception $e) {
            return $e;
        }

        return $response;
    } //Function ends

}
