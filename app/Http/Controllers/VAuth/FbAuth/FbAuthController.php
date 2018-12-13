<?php

namespace App\Http\Controllers\VAuth\Fb\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\FbAuth\FbAuthService;

class FbAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Facebook signin Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle facebook authentication
    |
    */
    private $fbAuthService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FbAuthService $fbAuthService)
    {
        $this->fbAuthService = $fbAuthService;
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

        $authResult = $this->fbAuthService->auth($authData);
        if($authResult->isSuccess()) {
            return $authResult->data;
        }

        return $authResult->toMyResponse();
    }
}
