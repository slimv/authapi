<?php
/**
 * Trait which help with permission checking. This trait will get permission data from request to check again the one you want to check
 */

namespace App\Traits\Session;
use DB;
use Illuminate\Support\Facades\Log;
use App\Model\CommonResponse;

trait JwtSessionTrait
{
    //Request
    public $jwtRequest;

    //App\Model\User
    public $jwtCurrentUser;

    //App\Model\PassportMode\PassportClient
    public $jwtCurrentClient;

    //array
    public $jwtPermissions;

    /**
     * Set the jwt data with session from request
     * @param [type] $request [description]
     */
    public function setJwtSessionFromRequest($request)
    {
        if(!$request) {
            return;
        }

        $this->jwtRequest = $request;
        $this->jwtCurrentUser = $request->jwt_user;
        $this->jwtCurrentClient = $request->jwt_client;
        $this->jwtPermissions = $request->jwt_user_permissions;

        if(method_exists($this, 'reloadMainRepositorySoftDeleteView')) {
            $this->reloadMainRepositorySoftDeleteView();
        }
    }
}