<?php

namespace App\Http\Controllers\VAuthAdministrator\Client\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Permission\ClientPermissionService;

class ClientPermissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Client Permission Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to Client Permission
    |
    */
    private $clientPermissionService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ClientPermissionService $clientPermissionService)
    {
        $this->clientPermissionService = $clientPermissionService;
    }

    /**
     * Api to get list of permission for selected client
     * Permissions:
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function permissions($clientId, Request $request)
    {
        $requestData = $request->all();
        $this->clientPermissionService->setJwtSessionFromRequest($request);
        $this->clientPermissionService->setClient($clientId);
        return $this->clientPermissionService->fetchAll($requestData)->toMyResponse();
    }

    /**
     * Api to create new permission
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function createPermission($clientId, Request $request)
    {
        $requestData = $request->only('key', 'description');
        $this->clientPermissionService->setJwtSessionFromRequest($request);
        $this->clientPermissionService->setClient($clientId);
        return $this->clientPermissionService->createPermission($requestData)->toMyResponse();
    }

    /**
     * Api to update permission
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $clientId [Selected client id]
     * @param  [string] $permissionScrubId [Selected permission scrub id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function updatePermission($clientId, $permissionScrubId, Request $request)
    {
        $requestData = $request->only('key', 'description');
        $this->clientPermissionService->setJwtSessionFromRequest($request);
        $this->clientPermissionService->setClient($clientId);
        return $this->clientPermissionService->updateClient($permissionScrubId, $requestData)->toMyResponse();
    }

    /**
     * Api to delete permissions of client
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function deletePermissions($clientId, Request $request)
    {
        $requestData = $request->only('ids');
        $permissionIds = json_decode($requestData['ids']);
        $this->clientPermissionService->setJwtSessionFromRequest($request);
        $this->clientPermissionService->setClient($clientId);
        return $this->clientPermissionService->deletePermissions($permissionIds)->toMyResponse();
    }
}
