<?php
/**
 * Service to handle user active code
 */

namespace App\Http\Service\Model;

use App\Http\Model\Table\UserDeviceRepository;
use App\Http\Model\Table\UserRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\UserDevice;
use VDateTime;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;

class UserDeviceService
{
    use JwtSessionTrait, PermissionTrait;

    /** @var UserDeviceRepository */
    private $userDeviceRepo;
    private $userRepo;

    /**
     * AuthService constructor.
     * @param UserDeviceRepository $userDeviceRepo
     */
    public function __construct(UserDeviceRepository $userDeviceRepo, UserRepository $userRepo)
    {
        $this->userDeviceRepo = $userDeviceRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Set user using its ids
     * Always call this function after JWT session have been set
     * @param [string] $userId [User Id]
     */
    public function setUser($userId)
    {
        if ($this->jwtCan(['user:view-deleted'])) {
            $this->userRepo->allowViewSoftDeleted();
        }

        $user = $this->userRepo->find($userId);
        if($user) {
            $this->userDeviceRepo->user = $user;
        }
    }

    /**
     * Function to get device of selected users
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function fetchAll($query)
    {
        $devices = $this->userDeviceRepo->items($query);

        return new CommonResponse(200, $devices);
    }

    /**
     * Register device for requested user
     * @param  [Request] $request    [Laravel request object]
     * @param  [array] $deviceData [Data of device]
     * @return [CommonResponse]       [Generated result]
     */
    public function registerDevice($request, $deviceData) {
        if(!$request || !$deviceData) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //first we need to remove uneccessary data
        $deviceData = $this->userDeviceRepo->stripPropertiesFromRequestData($deviceData);
        $deviceData['user_id'] = $user->id;

        //if device existed, we will simply update the last_access_at, otherwise we will create new one
        $existedDevice = UserDevice::where('user_id', $user->id)->where('device_id', $deviceData['device_id'])->first();
        if($existedDevice) {
            //update the data
            $result = $existedDevice->registerLastAccess();
            if($result) {
                return (new CommonResponse(200));
            } else {
                return (new CommonResponse(500, [], __("Cannot register device due to unexpected data")));
            }
        } else {
            //now we create the device
            $createResult = $this->userDeviceRepo->create($deviceData);

            return $createResult;
        }
    }
}