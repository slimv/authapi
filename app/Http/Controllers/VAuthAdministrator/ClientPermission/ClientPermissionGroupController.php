<?php

namespace App\Http\Controllers\VAuthAdministrator\Client\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Permission\ClientPermissionGroupService;

class ClientPermissionGroupController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Client Permission Group Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to Client Group Permission
    |
    */
    private $clientPermissionGroupService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ClientPermissionGroupService $clientPermissionGroupService)
    {
        $this->clientPermissionGroupService = $clientPermissionGroupService;
    }

    /**
     * Api to get list of group for selected client
     * Permissions:
     *  - client:view [required]
     *  - client:view-deleted [optional]
     *  - client:permission-view-deleted [optional]
     * @param  [string] $clientId [Selected client id]
     * @param  [string] $parentScrubId [Parent scrub id, or 'root'. If value is root, only get group is root ]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function groups($clientId, $parentScrubId, Request $request)
    {
        $requestData = $request->all();
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        $parentScrubId = ($parentScrubId == 'root') ? null : $parentScrubId;
        return $this->clientPermissionGroupService->fetchAll($parentScrubId, $requestData)->toMyResponse();
    }

    /**
     * Api to create new group permission
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function createGroup($clientId, Request $request)
    {
        $requestData = $request->only('name', 'description', 'parent_scrub_id', 'key');
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->createGroup($requestData)->toMyResponse();
    }

    /**
     * Api to update group
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $clientId [Selected client id]
     * @param  [string] $groupScrubId [Selected group scrub id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function updateGroup($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->only('name', 'description', 'parent_scrub_id', 'key');
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->updateGroup($groupScrubId, $requestData)->toMyResponse();
    }

    /**
     * Api to lock groups
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function lockGroups($clientId, Request $request)
    {
        $requestData = $request->only('ids');
        $groupIds = json_decode($requestData['ids']);
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->lockGroups($groupIds)->toMyResponse();
    }

    /**
     * Api to unlock groups
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     *  - client:permission-view-deleted [required]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function unlockGroups($clientId, Request $request)
    {
        $requestData = $request->only('ids');
        $groupIds = json_decode($requestData['ids']);
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->unlockGroups($groupIds)->toMyResponse();
    }

    /**
     * Api to delete group
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     *  - client:permission-view-deleted [required]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function deleteGroups($clientId, Request $request)
    {
        $requestData = $request->only('ids');
        $groupIds = json_decode($requestData['ids']);
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->deleteGroups($groupIds)->toMyResponse();
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
    public function groupPermissions($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all();
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->getGroupPermissions($groupScrubId, $requestData)->toMyResponse();
    }

    /**
     * Api to add permissions into group
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
    public function assignPermissionsToGroup($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all('permissionScrubIds');
        $permissionScrubIds = json_decode($requestData['permissionScrubIds']);
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->assignPermissionsToGroup($groupScrubId, $permissionScrubIds)->toMyResponse();
    }

    /**
     * Api to remove permissions from group
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
    public function removePermissionsFromGroup($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all('permissionScrubIds');
        $permissionScrubIds = json_decode($requestData['permissionScrubIds']);
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->removePermissionsFromGroup($groupScrubId, $permissionScrubIds)->toMyResponse();
    }

    /**
     * Api to get group available permissions
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
    public function groupAvailablePermissions($clientId, $groupScrubId, Request $request)
    {
        $requestData = $request->all();
        $this->clientPermissionGroupService->setJwtSessionFromRequest($request);
        $this->clientPermissionGroupService->setClient($clientId);
        return $this->clientPermissionGroupService->getGroupAvailablePermissions($groupScrubId, $requestData)->toMyResponse();
    }
}
