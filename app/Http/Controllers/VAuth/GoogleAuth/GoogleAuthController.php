<?php

namespace App\Http\Controllers\VAuth\Google\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\GoogleAuth\GoogleAuthService;

class GoogleAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Google signin Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle google authentication
    |
    */
    private $googleAuthService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * Api to login using facebook.
     * Permissions: No permission require
     * Note: If we cannot find any link account with given facebook information, we will create new user automatically
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function auth(Request $request)
    {
        $authData = $request->only('token', 'secret');

        $authResult = $this->googleAuthService->auth($authData);
        if($authResult->isSuccess()) {
            return $authResult->data;
        }

        return $authResult->toMyResponse();
    }
}
