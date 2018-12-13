<?php
/**
 * Service to handle permission group (private only)
 */

namespace App\Http\Service\Client\Group;

use App\Http\Model\Table\ClientRepository;
use App\Http\Model\Table\ClientUserGroupMidRepository;
use App\Http\Model\Table\ClientPermissionGroupRepository;
use App\Http\Model\Table\ClientAvailableUserGroupRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\Group;
use App\Model\UserGroup;
use App\Model\PassportMode\PassportClient;
use VDateTime;
use VStringGenerator;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;
use App\Model\Traits\ModelServiceSoftDeleteTrait;
use Illuminate\Support\Facades\Log;

class ClientGroupUserService
{
    use JwtSessionTrait, PermissionTrait, ModelServiceSoftDeleteTrait;

    /** @var ClientPermissionGroupRepository */
    private $clientUserGroupMidRepository;
    private $clientRepo;
    private $clientPermissionGroupRepository;
    private $clientAvailableUserGroupRepository;

    /**
     * AuthService constructor.
     * @param ClientPermissionGroupRepository $clientRepo
     */
    public function __construct(ClientRepository $clientRepo, ClientUserGroupMidRepository $clientUserGroupMidRepository, ClientPermissionGroupRepository $clientPermissionGroupRepository, ClientAvailableUserGroupRepository $clientAvailableUserGroupRepository)
    {
        $this->clientUserGroupMidRepository = $clientUserGroupMidRepository;
        $this->clientRepo = $clientRepo;
        $this->clientPermissionGroupRepository = $clientPermissionGroupRepository;
        $this->clientAvailableUserGroupRepository = $clientAvailableUserGroupRepository;
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
            $this->clientUserGroupMidRepository->client = $client;
            $this->clientPermissionGroupRepository->client = $client;
            $this->clientAvailableUserGroupRepository->client = $client;
        }
    }

    /**
     * Function to get a group users
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [string] $groupScrubId [Group to get users]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function getGroupUsers($groupScrubId, $query)
    {
        if(!$groupScrubId) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $group = $this->clientPermissionGroupRepository->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        $this->clientUserGroupMidRepository->group = $group;
        $this->clientUserGroupMidRepository->enableClosure = true;
        $users = $this->clientUserGroupMidRepository->items($query);

        return new CommonResponse(200, $users);
    }

    /**
     * Function to assign user into groups
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [string] $groupScrubId [Id of group]
     * @param  [array] $userScrubIds [List of user to be assigned]
     * @return [CommonResponse]       [Generated result]
     */
    public function assignUsersToGroup($groupScrubId, $userScrubIds) 
    {
        if(!$groupScrubId || !$userScrubIds || count($userScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //get group
        $group = $this->clientPermissionGroupRepository->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $this->clientUserGroupMidRepository->group = $group;

        //now we assign the permissions
        $assignResult = $this->clientUserGroupMidRepository->assign($userScrubIds);

        return $assignResult;
    }

    /**
     * Function to remove user from groups
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $groupData [Data of group to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function removeUsersFromGroup($groupScrubId, $userScrubIds) 
    {
        if(!$groupScrubId || !$userScrubIds || count($userScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //get group
        $group = $this->clientPermissionGroupRepository->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $this->clientUserGroupMidRepository->group = $group;

        //now we assign the permissions
        $assignResult = $this->clientUserGroupMidRepository->unassign($userScrubIds);

        return $assignResult;
    }

    /**
     * Function to get all user which can be added to this group
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [string] $groupScrubId [Group to get permissions]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function getGroupAvailableUsers($groupScrubId, $query)
    {
        if(!$groupScrubId) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }
        $group = $this->clientPermissionGroupRepository->getByScrubId($groupScrubId);
        if(!$group) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        $this->clientAvailableUserGroupRepository->targetGroup = $group;
        $users = $this->clientAvailableUserGroupRepository->items($query);

        return new CommonResponse(200, $users);
    }
}