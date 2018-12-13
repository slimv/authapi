<?php
/**
 * Service to handle permission group (private only)
 */

namespace App\Http\Service\ClientGrant\Permission;

use App\Http\Model\Table\ClientRepository;
use App\Http\Model\Table\ClientPermissionGroupRepository;
use App\Http\Model\Table\ClientPermissionRepository;
use App\Http\Model\Table\ClientPermissionGroupMidRepository;
use App\Http\Model\Table\ClientUserGroupMidRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\Group;
use App\Model\Permission;
use App\Model\GroupPermission;
use App\Model\PassportMode\PassportClient;
use VDateTime;
use VStringGenerator;

class ClientPermissionGroupService
{
    /** @var ClientPermissionGroupRepository */
    private $clientPermissionGroupRepo;
    private $clientPermissionRepo;
    private $clientRepo;
    private $clientPermissionGroupMidRepository;
    private $clientUserGroupMidRepository;
    private $client;

    /**
     * AuthService constructor.
     * @param ClientPermissionGroupRepository $clientRepo
     */
    public function __construct(ClientPermissionGroupRepository $clientPermissionGroupRepo, ClientPermissionRepository $clientPermissionRepo, ClientRepository $clientRepo, ClientPermissionGroupMidRepository $clientPermissionGroupMidRepository, ClientUserGroupMidRepository $clientUserGroupMidRepository)
    {
        $this->clientPermissionGroupRepo = $clientPermissionGroupRepo;
        $this->clientPermissionRepo = $clientPermissionRepo;
        $this->clientRepo = $clientRepo;
        $this->clientPermissionGroupMidRepository = $clientPermissionGroupMidRepository;
        $this->clientUserGroupMidRepository = $clientUserGroupMidRepository;
    }

    /**
     * Set client using its ids
     * Always call this function after JWT session have been set
     * @param [string] $clientId [Client Id]
     */
    public function setClient($clientId)
    {
        $client = $this->clientRepo->find($clientId);
        if($client) {
            $this->client = $client;
            $this->clientPermissionGroupRepo->client = $client;
            $this->clientPermissionRepo->client = $client;
            $this->clientPermissionGroupMidRepository->client = $client;
            $this->clientUserGroupMidRepository->client = $client;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to assign selected users into selected groups
     * @param  [array] $userIds  [List of user Ids]
     * @param  [array] $groupKeys [List of group keys]
     * @return [CommonResponse]           [The response]
     */
    public function assignUsersToGroups($userIds, $groupKeys)
    {
        if(!$this->client) {
            return (new CommonResponse(400, [], __("Invalid client data")));
        }

        //check the users data
        $users = User::whereIn('id', $userIds)->get();
        if(count($users) != count($userIds)) {
            return (new CommonResponse(400, [], __("Users data contain invalid data")));
        }

        //check the groups data
        $groups = Group::whereIn('key', $groupKeys)->where('client_id', $this->client->id)->get();
        if(count($groups) != count($groupKeys)) {
            return (new CommonResponse(400, [], __("Groups data contain invalid data")));
        }

        $result = $this->clientUserGroupMidRepository->assignUsersIntoGroups($users, $groups);
        return $result;
    }
}