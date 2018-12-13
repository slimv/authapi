<?php
/**
 * Service to handle curret user profile
 */

namespace App\Http\Service\Auth;

use App\Model\CommonResponse;
use App\Model\User;
use VDateTime;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;

class ProfileService
{
    use JwtSessionTrait, PermissionTrait;

    /**
     * AuthService constructor.
     * @param UserDeviceRepository $userDeviceRepo
     */
    public function __construct()
    {
    }

    /**
     * Function to return current user profile
     * Note: this function is only work if user already actived
     * Permissions:
     *  - auth:access [required]
     * @return [CommonResponse]        [Response]
     */
    public function getCurrentUserProfile()
    {
        return (new CommonResponse(200, $this->jwtCurrentUser));
    }
}