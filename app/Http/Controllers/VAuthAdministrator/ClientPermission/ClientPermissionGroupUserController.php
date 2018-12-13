<?php

namespace App\Http\Controllers\VAuthAdministrator\Client\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Client\Group\ClientGroupUserService;

class ClientPermissionGroupUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Client User Group Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to Client Group User
    |
    */
    private $clientGroupUserService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ClientGroupUserService $clientGroupUserService)
    {
        $this->clientGroupUserService = $clientGroupUserService;
    }

    /**
     * Api to get group permissions
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     *  - client:permission-view-deleted [optional]
     * @param  [string] $clientId [Selected client id]
     * @param  [string] $groupScrubId [Selected group scrub id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function groupUsers($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all();
        $this->clientGroupUserService->setJwtSessionFromRequest($request);
        $this->clientGroupUserService->setClient($clientId);
        return $this->clientGroupUserService->getGroupUsers($groupScrubId, $requestData)->toMyResponse();
    }

    /**
     * Api to add users into group
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     *  - client:permission-view-deleted [optional]
     * @param  [string] $clientId [Selected client id]
     * @param  [string] $groupScrubId [Selected group scrub id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function assignUsersToGroup($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all('userScrubIds');
        $userScrubIds = json_decode($requestData['userScrubIds']);
        $this->clientGroupUserService->setJwtSessionFromRequest($request);
        $this->clientGroupUserService->setClient($clientId);
        return $this->clientGroupUserService->assignUsersToGroup($groupScrubId, $userScrubIds)->toMyResponse();
    }

    /**
     * Api to remove users from group
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     *  - client:permission-view-deleted [optional]
     * @param  [string] $clientId [Selected client id]
     * @param  [string] $groupScrubId [Selected group scrub id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function removeUsersFromGroup($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all('userScrubIds');
        $userScrubIds = json_decode($requestData['userScrubIds']);
        $this->clientGroupUserService->setJwtSessionFromRequest($request);
        $this->clientGroupUserService->setClient($clientId);
        return $this->clientGroupUserService->removeUsersFromGroup($groupScrubId, $userScrubIds)->toMyResponse();
    }

    /**
     * Api to get group available users
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     *  - client:permission-view-deleted [optional]
     * @param  [string] $clientId [Selected client id]
     * @param  [string] $groupScrubId [Selected group scrub id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function groupAvailableUsers($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all();
        $this->clientGroupUserService->setJwtSessionFromRequest($request);
        $this->clientGroupUserService->setClient($clientId);
        return $this->clientGroupUserService->getGroupAvailableUsers($groupScrubId, $requestData)->toMyResponse();
    }
}
