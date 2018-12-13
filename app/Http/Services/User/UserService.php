<?php
/**
 * Service to handle user (private only)
 */

namespace App\Http\Service\User;

use App\Http\Model\Table\UserRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\PassportMode\PassportClient;
use VDateTime;
use VStringGenerator;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;
use App\Model\Traits\ModelServiceSoftDeleteTrait;

class UserService
{
    use JwtSessionTrait, PermissionTrait, ModelServiceSoftDeleteTrait;

    /** @var UserRepository */
    private $userRepo;

    /**
     * AuthService constructor.
     * @param UserRepository $clientRepo
     */
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;

        //ModelServiceSoftDeleteTrait properties
        $this->modelMainSoftDeleteRepository = $this->userRepo;
        $this->modelPermissionToViewDeletedName = 'user:view-deleted';
    }

    /**
     * Function to query users.
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:view-deleted [optional]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function fetchAll($query)
    {
        $users = $this->userRepo->items($query);

        return new CommonResponse(200, $users);
    }

    /**
     * Function to update user with given data
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:update [required]
     * @param  [string] $userId [Selected user id]
     * @param  [array] $userData [Data of user to be updated]
     * @return [CommonResponse]       [Generated result]
     */
    public function updateUser($userId, $updateData) 
    {
        if(!$updateData || !$userId) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $currentUser = $this->jwtCurrentUser;
        if(!$currentUser) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->userRepo->find($userId);
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid user data")));
        }

        //first we need to remove uneccessary data
        $updateData = $this->userRepo->stripPropertiesFromRequestData($updateData);

        //now we update the user
        $updateResult = $this->userRepo->update($user, $updateData);

        return $updateResult;
    }

    /**
     * Function to lock users
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:lock [required]
     * @param  [array] $userIds [List of user ids need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function lockUsers($userIds)
    {
        if(!$userIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $lookUpResult = $this->userRepo->findIn($userIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $users = $lookUpResult->data;

        //now we update the user
        $lockResult = $this->userRepo->delete($users, false, true);
        return $lockResult;
    }

    /**
     * Function to unlock users
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:unlock [required]
     *  - user:view-deleted [required]
     * @param  [array] $userIds [List of user ids need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function unlockUsers($userIds)
    {
        if(!$userIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $this->userRepo->allowViewSoftDeleted();

        $lookUpResult = $this->userRepo->findIn($userIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $users = $lookUpResult->data;

        //now we update the user
        $lockResult = $this->userRepo->recovery($users, true);
        return $lockResult;
    }

    /**
     * Function to delete users
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - user:view [required]
     *  - user:delete [required]
     * @param  [array] $userIds [List of user ids need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function deleteUsers($userIds)
    {
        if(!$userIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $lookUpResult = $this->userRepo->findIn($userIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $users = $lookUpResult->data;

        //now we delete the client
        $deleteResult = $this->userRepo->delete($users, true, true);
        return $deleteResult;
    }
}