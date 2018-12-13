<?php
/**
 * Service to handle permission group (private only)
 */

namespace App\Http\Service\Permission;

use App\Http\Model\Table\ClientRepository;
use App\Http\Model\Table\ClientPermissionGroupRepository;
use App\Http\Model\Table\ClientPermissionRepository;
use App\Http\Model\Table\ClientPermissionGroupMidRepository;
use App\Http\Model\Table\ClientAvailablePermissionGroupRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\Permission;
use App\Model\GroupPermission;
use App\Model\PassportMode\PassportClient;
use VDateTime;
use VStringGenerator;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;
use App\Model\Traits\ModelServiceSoftDeleteTrait;
use Illuminate\Support\Facades\Log;

class ClientPermissionGroupService
{
    use JwtSessionTrait, PermissionTrait, ModelServiceSoftDeleteTrait;

    /** @var ClientPermissionGroupRepository */
    private $clientPermissionGroupRepo;
    private $clientPermissionRepo;
    private $clientRepo;
    private $clientPermissionGroupMidRepository;
    private $clientAvailablePermissionGroupRepository;

    /**
     * AuthService constructor.
     * @param ClientPermissionGroupRepository $clientRepo
     */
    public function __construct(ClientPermissionGroupRepository $clientPermissionGroupRepo, ClientPermissionRepository $clientPermissionRepo, ClientRepository $clientRepo, ClientPermissionGroupMidRepository $clientPermissionGroupMidRepository, ClientAvailablePermissionGroupRepository $clientAvailablePermissionGroupRepository)
    {
        $this->clientPermissionGroupRepo = $clientPermissionGroupRepo;
        $this->clientPermissionRepo = $clientPermissionRepo;
        $this->clientRepo = $clientRepo;
        $this->clientPermissionGroupMidRepository = $clientPermissionGroupMidRepository;
        $this->clientAvailablePermissionGroupRepository = $clientAvailablePermissionGroupRepository;

        //ModelServiceSoftDeleteTrait properties
        $this->modelMainSoftDeleteRepository = $this->clientPermissionGroupRepo;
        $this->modelPermissionToViewDeletedName = 'client:permission-view-deleted';
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
            $this->clientPermissionGroupRepo->client = $client;
            $this->clientPermissionRepo->client = $client;
            $this->clientPermissionGroupMidRepository->client = $client;
            $this->clientAvailablePermissionGroupRepository->client = $client;
        }
    }

    /**
     * Function to get permission group of current client
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [string|null] $parentScrubId [Parent category Ids, if null we will only get group which is root. Otherwise return selected parent category children only]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function fetchAll($parentScrubId, $query)
    {
        $extraCondition = null;
        if($parentScrubId) {
            $parent = $this->clientPermissionGroupRepo->getByScrubId($parentScrubId);
            if(!$parent) {
                return (new CommonResponse(400, [], __("Invalid parent data")));
            }

            $extraCondition = [
                ["field" => "parent_id", "op" => "equal", "value" => $parent->id]
            ];

        } else {
            $extraCondition = [
                ["field" => "parent_id", "op" => "null"]
            ];
        }
        
        $groups = $this->clientPermissionGroupRepo->items($query, $extraCondition);

        return new CommonResponse(200, $groups);
    }

    /**
     * Function to create new group for client
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $groupData [Data of group to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function createGroup($groupData) 
    {
        if(!$groupData || !isset($groupData['key'])) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //check duplicate
        $duplicate = $this->clientPermissionGroupRepo->getGroupByKey($groupData['key']);
        if($duplicate) {
            return (new CommonResponse(400, [], __("There is already another group with this name")));
        }

        //if parent is set, make sure it is valid
        $parent = null;
        if(isset($groupData['parent_scrub_id'])) {
            $parent = $this->clientPermissionGroupRepo->getByScrubId($groupData['parent_scrub_id']);
            if(!$parent) {
                return (new CommonResponse(400, [], __("Invalid parent data")));
            }
        }

        //first we need to remove uneccessary data
        $groupData = $this->clientPermissionGroupRepo->stripPropertiesFromRequestData($groupData);
        $groupData['client_id'] = $this->clientPermissionGroupRepo->client->id;
        if($parent) {
            $groupData['parent_id'] = $parent->id;
        }

        //now we create the client
        $createResult = $this->clientPermissionGroupRepo->create($groupData);

        return $createResult;
    }

    /**
     * Function to update permission with given data
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $groupScrubId [Group to be updated]
     * @param  [array] $updateData [Data of group to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function updateGroup($groupScrubId, $updateData) 
    {
        if(!$updateData || !$groupScrubId) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $group = $this->clientPermissionGroupRepo->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        //if parent is set, make sure it is valid
        $parent = null;
        if(isset($updateData['parent_scrub_id'])) {
            $parent = $this->clientPermissionGroupRepo->getByScrubId($updateData['parent_scrub_id']);
            if(!$parent) {
                return (new CommonResponse(400, [], __("Invalid parent data")));
            }
        }

        //first we need to remove uneccessary data
        $updateData = $this->clientPermissionGroupRepo->stripPropertiesFromRequestData($updateData);
        if($parent) {
            $updateData['parent_id'] = $parent->id;
        }

        //now we update the group
        $updateResult = $this->clientPermissionGroupRepo->update($group, $updateData);

        return $updateResult;
    }

    /**
     * Function to lock groups
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     *  - client:permission-view-deleted [optional]
     * @param  [array] $groupScrubIds [List of group scrub ids need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function lockGroups($groupScrubIds)
    {
        if(!$groupScrubIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $lookUpResult = $this->clientPermissionGroupRepo->getScrubIdIn($groupScrubIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $groups = $lookUpResult->data;

        //now we update the group
        $lockResult = $this->clientPermissionGroupRepo->delete($groups, false, true);
        return $lockResult;
    }

    /**
     * Function to unlock groups
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:unlock [required]
     *  - client:permission-view-deleted [required]
     * @param  [array] $groupScrubIds [List of group scrub ids need to be unlocked]
     * @return [CommonResponse]       [Generated result]
     */
    public function unlockGroups($groupScrubIds)
    {
        if(!$groupScrubIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $this->clientPermissionGroupRepo->allowViewSoftDeleted();

        $lookUpResult = $this->clientPermissionGroupRepo->getScrubIdIn($groupScrubIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $groups = $lookUpResult->data;

        //now we update the groups
        $lockResult = $this->clientPermissionGroupRepo->recovery($groups, true);
        return $lockResult;
    }

    /**
     * Function to delete group
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     *  - client:permission-view-deleted [optional]
     * @param  [array] $groupScrubIds [List of group scrub id need to be deleted]
     * @return [CommonResponse]       [Generated result]
     */
    public function deleteGroups($groupScrubIds)
    {
        if(!$groupScrubIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $lookUpResult = $this->clientPermissionGroupRepo->getScrubIdIn($groupScrubIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $groups = $lookUpResult->data;

        //now we delete the client
        $deleteResult = $this->clientPermissionGroupRepo->delete($groups, true, true);
        return $deleteResult;
    }

    /**
     * Function to get a group permissions
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [string] $groupScrubId [Group to get permissions]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function getGroupPermissions($groupScrubId, $query)
    {
        if(!$groupScrubId) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $group = $this->clientPermissionGroupRepo->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        $this->clientPermissionGroupMidRepository->group = $group;
        $this->clientPermissionGroupMidRepository->enableClosure = true;
        $permissions = $this->clientPermissionGroupMidRepository->items($query);

        return new CommonResponse(200, $permissions);
    }

    /**
     * Function to assign permission into groups
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $groupData [Data of group to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function assignPermissionsToGroup($groupScrubId, $permissionScrubIds) 
    {
        if(!$groupScrubId || !$permissionScrubIds || count($permissionScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //get group
        $group = $this->clientPermissionGroupRepo->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $this->clientPermissionGroupMidRepository->group = $group;

        //now we assign the permissions
        $assignResult = $this->clientPermissionGroupMidRepository->assign($permissionScrubIds);

        return $assignResult;
    }

    /**
     * Function to remove permission from groups
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $groupData [Data of group to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function removePermissionsFromGroup($groupScrubId, $permissionScrubIds) 
    {
        if(!$groupScrubId || !$permissionScrubIds || count($permissionScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //get group
        $group = $this->clientPermissionGroupRepo->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $this->clientPermissionGroupMidRepository->group = $group;

        //now we assign the permissions
        $assignResult = $this->clientPermissionGroupMidRepository->unassign($permissionScrubIds);

        return $assignResult;
    }

    /**
     * Function to get all permission which can be added to this group
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [string] $groupScrubId [Group to get permissions]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function getGroupAvailablePermissions($groupScrubId, $query)
    {
        if(!$groupScrubId) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $group = $this->clientPermissionGroupRepo->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        $this->clientAvailablePermissionGroupRepository->targetGroup = $group;
        $permissions = $this->clientAvailablePermissionGroupRepository->items($query);

        return new CommonResponse(200, $permissions);
    }
}