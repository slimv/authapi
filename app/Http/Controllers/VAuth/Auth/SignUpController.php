<?php

namespace App\Http\Controllers\VAuth\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Auth\AuthService;

class SignUpController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Signup Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to Signup
    |
    */
    private $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Api to sign up new user
     * Permissions: No permission require
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function signup(Request $request)
    {
        $signupData = $request->only('email', 'password', 'first_name', 'last_name');

        $signupResult = $this->authService->signUp($signupData);

        return $signupResult->toMyResponse();
    }

    /**
     * Api to active user using given code
     * @return [type] [description]
     */
    public function activeSignupCode($code, Request $request)
    {
        $activeResult = $this->authService->activeSignupCode($code);

        if($activeResult->isSuccess()) {
            return redirect('code/active/success');
        } else {
            return redirect('code/active/failure')->with(['error_message', $activeResult->message]);
        }
    }
}
