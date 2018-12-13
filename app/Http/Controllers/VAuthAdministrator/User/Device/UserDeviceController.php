<?php

namespace App\Http\Controllers\VAuthAdministrator\User\Device;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Model\UserDeviceService;

class UserDeviceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to User
    |
    */
    private $userDeviceService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserDeviceService $userDeviceService)
    {
        $this->userDeviceService = $userDeviceService;
    }

    /**
     * Api to get list of devices for user
     * Permissions:
     *  - user:view [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function devices($userId, Request $request)
    {
        $requestData = $request->all();
        $this->userDeviceService->setJwtSessionFromRequest($request);
        $this->userDeviceService->setUser($userId);
        return $this->userDeviceService->fetchAll($requestData)->toMyResponse();
    }
}
