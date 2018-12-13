<?php

namespace App\Http\Controllers\VAuthAdministrator\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\User\UserService;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to User
    |
    */
    private $userService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Api to get list of users in the system
     * Permissions:
     *  - user:view [required]
     *  - user:view-deleted [optional]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function users(Request $request)
    {
        $requestData = $request->all();
        $this->userService->setJwtSessionFromRequest($request);
        return $this->userService->fetchAll($requestData)->toMyResponse();
    }

    /**
     * Api to update user
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:update [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function updateUser($userId, Request $request)
    {
        $requestData = $request->only('email', 'password', 'first_name', 'last_name');
        $this->userService->setJwtSessionFromRequest($request);
        return $this->userService->updateUser($userId, $requestData)->toMyResponse();
    }

    /**
     * Api to lock users
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:lock [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function lockUsers(Request $request)
    {
        $requestData = $request->only('ids');
        $userIds = json_decode($requestData['ids']);
        $this->userService->setJwtSessionFromRequest($request);
        return $this->userService->lockUsers($userIds)->toMyResponse();
    }

    /**
     * Api to unlock users
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:unlock [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function unlockUsers(Request $request)
    {
        $requestData = $request->only('ids');
        $userIds = json_decode($requestData['ids']);
        $this->userService->setJwtSessionFromRequest($request);
        return $this->userService->unlockUsers($userIds)->toMyResponse();
    }

    /**
     * Api to delete users
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:delete [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function deleteUsers(Request $request)
    {
        $requestData = $request->only('ids');
        $userIds = json_decode($requestData['ids']);
        $this->userService->setJwtSessionFromRequest($request);
        return $this->userService->deleteUsers($userIds)->toMyResponse();
    }
}
