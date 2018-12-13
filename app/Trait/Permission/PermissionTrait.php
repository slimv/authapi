<?php
/**
 * Trait which help with permission checking. This trait will get permission data from request to check again the one you want to check
 */

namespace App\Traits;
use DB;
use Illuminate\Support\Facades\Log;
use App\Model\CommonResponse;

trait PermissionTrait
{
	/**
	 * This function will check current request to see if it have selecte permission or not
	 * @param  [Request]  $request           [User request]
	 * @param  [array]  $permissionCodes    [List of permission to check]
	 * @param  boolean $matchAgainAll [If true, only return true if all permission are present. Otherwise only one permission match to return true]
	 * @param  boolean $shouldReturnError [If true, if permission is invalid, return 401 error code. If false, simply return true or false]
	 * @return boolean                    [True if valid]
	 */
    public function isSessionHavePermission($request, $permissionCodes, $matchAgainAll = true, $shouldReturnError = false)
    {
    	if(!$permissionCodes || count($permissionCodes) == 0) {
    		if($shouldReturnError) {
    			return (new CommonResponse(401, [], "You dont have require permission to perform this action"));
    		} else {
    			return false;
    		}
    	}

    	if(!isset($request['jwt_user_permissions']) || !$request->jwt_user_permissions || count($request->jwt_user_permissions) == 0) {
    		if($shouldReturnError) {
    			return (new CommonResponse(401, [], "You dont have require permission to perform this action"));
    		} else {
    			return false;
    		}
    	}

    	$permissions = $request->jwt_user_permissions;
    	$permissionCountIntersect = count(array_intersect($permissions, $permissionCodes));
    	if($matchAgainAll) {
    		if($permissionCountIntersect == count($permissionCodes)) {
    			if($shouldReturnError) {
	    			return (new CommonResponse(200));
	    		} else {
	    			return true;
	    		}
    		} else {
    			if($shouldReturnError) {
	    			return (new CommonResponse(401, [], "You dont have require permission to perform this action"));
	    		} else {
	    			return false;
	    		}
    		}
    	} else {
    		if($permissionCountIntersect >= 1) {
    			if($shouldReturnError) {
	    			return (new CommonResponse(200));
	    		} else {
	    			return true;
	    		}
    		} else {
    			if($shouldReturnError) {
	    			return (new CommonResponse(401, [], "You dont have require permission to perform this action"));
	    		} else {
	    			return false;
	    		}
    		}
    	}

    	if($shouldReturnError) {
			return (new CommonResponse(200));
		} else {
			return true;
		}
    }

    /**
     * Check if current user can do the selected permission or not
     * NOTE: require JwtSessionTrait to work
     * @param  [array]  $permissionCodes    [List of permission to check]
     * @param  boolean $matchAgainAll [If true, only return true if all permission are present. Otherwise only one permission match to return true]
     * @param  boolean $shouldReturnError [If true, if permission is invalid, return 401 error code. If false, simply return true or false]
     * @return boolean                    [True if valid]
     */
    public function jwtCan($permissionCodes, $matchAgainAll = true)
    {
        return $this->isSessionHavePermission($this->jwtRequest, $permissionCodes, $matchAgainAll, false);
    }

    /**
     * Check if current user have been actived or not
     * @return CommonResponse [Response]
     */
    public function isSessionVerified($request)
    {
        $user = $request->jwt_user;

        if(!$user || $user->status != 'actived') {
            return (new CommonResponse(401, [], "Your account havent been actived yet"));
        } else {
            return (new CommonResponse(200));
        }
    }
}