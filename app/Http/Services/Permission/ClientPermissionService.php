<?php
/**
 * Service to handle permission (private only)
 */

namespace App\Http\Service\Permission;

use App\Http\Model\Table\ClientRepository;
use App\Http\Model\Table\ClientPermissionRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\Permission;
use App\Model\PassportMode\PassportClient;
use VDateTime;
use VStringGenerator;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;

class ClientPermissionService
{
    use JwtSessionTrait, PermissionTrait;

    /** @var ClientPermissionRepository */
    private $clientPermissionRepo;
    private $clientRepo;

    /**
     * AuthService constructor.
     * @param ClientPermissionRepository $clientRepo
     */
    public function __construct(ClientPermissionRepository $clientPermissionRepo, ClientRepository $clientRepo)
    {
        $this->clientPermissionRepo = $clientPermissionRepo;
        $this->clientRepo = $clientRepo;
    }

    /**
     * Set client using its ids
     * Always call this function after JWT session have been set
     * @param [string] $clientId [Client Id]
     */
    public function setClient($clientId)
    {
        if ($this->jwtCan(['client:view-deleted'])) {
            $this->clientRepo->allowViewSoftDeleted();
        }

        $client = $this->clientRepo->find($clientId);
        if($client) {
            $this->clientPermissionRepo->client = $client;
        }
    }

    /**
     * Function to get permission of current client
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function fetchAll($query)
    {
        $permissions = $this->clientPermissionRepo->items($query);

        return new CommonResponse(200, $permissions);
    }

    /**
     * Function to create new permission for client
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $permissionData [Data of permission to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function createPermission($permissionData) 
    {
        if(!$permissionData || !isset($permissionData['key'])) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //check duplicate
        $duplicate = $this->clientPermissionRepo->getPermissionByKey($permissionData['key']);
        if($duplicate) {
            return (new CommonResponse(400, [], __("This key have been used")));
        }

        //first we need to remove uneccessary data
        $permissionData = $this->clientPermissionRepo->stripPropertiesFromRequestData($permissionData);
        $permissionData['client_id'] = $this->clientPermissionRepo->client->id;

        //now we create the client
        $createResult = $this->clientPermissionRepo->create($permissionData);

        return $createResult;
    }

    /**
     * Function to update permission with given data
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $clientData [Data of permission to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function updateClient($permissionScrubId, $updateData) 
    {
        if(!$updateData || !$permissionScrubId) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $permission = $this->clientPermissionRepo->getByScrubId($permissionScrubId);
        if(!$permission) {
            return (new CommonResponse(400, [], __("Invalid permission data")));
        }

        //first we need to remove uneccessary data
        $updateData = $this->clientPermissionRepo->stripPropertiesFromRequestData($updateData);

        //now we update the permission
        $updateResult = $this->clientPermissionRepo->update($permission, $updateData);

        return $updateResult;
    }

    /**
     * Function to delete permission
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $permissionScrubIds [List of permission scrub id need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function deletePermissions($permissionScrubIds)
    {
        if(!$permissionScrubIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $lookUpResult = $this->clientPermissionRepo->getScrubIdIn($permissionScrubIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $permissions = $lookUpResult->data;

        //now we delete the client
        $deleteResult = $this->clientPermissionRepo->delete($permissions, true, true);
        return $deleteResult;
    }
}