<?php

namespace App\Http\Controllers\VAuth\Device;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Model\UserDeviceService;

class DeviceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle devices relate api (public api only)
    |
    */
    private $deviceService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserDeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Api to register device
     * Permissions:
     * - Require logined
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function registerDevice(Request $request)
    {
        $deviceData = $request->only('device_type', 'device_name', 'device_id');
        $this->deviceService->setJwtSessionFromRequest($request);

        $registerResult = $this->deviceService->registerDevice($request, $deviceData);
        //clear the data
        $registerResult->data = null;

        return $registerResult->toMyResponse();
    }
}
