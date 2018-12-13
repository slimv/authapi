<?php

namespace App\Http\Controllers\VAuth\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Auth\PasswordService;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle password changing
    |
    */
    private $passService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PasswordService $passService)
    {
        $this->passService = $passService;
    }

    /**
     * Api to submit request to change password when you forgot password
     * Permissions: No permission require
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function forgotPassword(Request $request)
    {
        $signupData = $request->only('email');
        $email = $signupData['email'];

        $submitResult = $this->passService->submitForgotPasswordRequest($email);
        //clear the data
        $submitResult->data = null;

        return $submitResult->toMyResponse();
    }

    /**
     * Api to update password using given code
     * Permissions: No permission require
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function updateForgotPassword(Request $request)
    {
        $requestData = $request->only('password', 'code');
        $password = $requestData['password'];
        $code = $requestData['code'];

        $submitResult = $this->passService->activeForgotPasswordCode($code, $password);

        return $submitResult->toMyResponse();
    }
}
