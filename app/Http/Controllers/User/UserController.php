<?php

namespace App\Http\Controllers\User;

use Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

use App\Models\User\User;
use Ellaisys\Cognito\Auth\RegistersUsers;
use Ellaisys\Cognito\Auth\SendsPasswordResetEmails;
use Ellaisys\Cognito\Auth\ResetsPasswords;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Routing\Controller as BaseController;

use Exception;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends BaseController
{
    use RegistersUsers;
    use SendsPasswordResetEmails;
    use ResetsPasswords;

    public function webRegister(Request $request)
    {
        $validator = $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:64|unique:users',
            'phone' => 'required|string|max:64',
            'password' => 'required|confirmed|min:6|max:64',
        ]);

        //User::create($request->only('name', 'email', 'password'));
        $data = $request->only('first_name', 'email', 'password');
        $data['name'] = $data['first_name'];
        unset($data['first_name']);

        //Create credentials object
        $collection = collect($request->all());

        //Register User
        if ($cognitoRegistered=$this->createCognitoUser($collection)) {
            User::create($data);
        } //End if


        return view('login');
    }


    public function sendPasswordResetEmail(Request $request)
    {
        //Method with SendsPasswordResetEmails trait
        if ($this->sendCognitoResetLinkEmail($request['email'])) {

        } //End if
    }


    public function actionResetPasswordCode(Request $request)
    {
        return $this->reset($request);
    }

}