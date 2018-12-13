<?php
/**
 * User Group Repository. This class will handle all the user-group works
 */
namespace App\Http\Model\Table;

use App\Model\Repository\CoreRepository;
use App\Model\PassportModel\PassportClient;
use App\Model\Group;
use App\Model\User;
use App\Model\UserGroup;
use Illuminate\Support\Facades\Log;
use App\Model\Traits\ModelRepositoryScrubIdTrait;
use App\Model\CommonResponse;
use DB;

class ClientUserGroupMidRepository extends CoreRepository
{
    use ModelRepositoryScrubIdTrait;

    public $client = null;
    public $group = null;
    public $enableClosure = false;      //if true, when filter with group will check for its closure

    /**
     * 
     */
    public function __construct()
    {
        
    }

    public function getModel($origin = false)
    {
        $model = new UserGroup;
        if($origin) {
            return $model;
        }

        $model = $model->join('users', 'user_id', '=', 'users.id');

        if($this->group) {
            if($this->enableClosure) {
                //this will work for closure
                if(method_exists($this->group, 'getClosureTreeIdList')) {
                    $groupFamilyIds = $this->group->getClosureTreeIdList();
                    $model = $model->whereIn('group_id', $groupFamilyIds);
                }
            } else {
                $model = $model->where('group_id', $this->group->id);
            }
        }

        return $model;
    }

    public function getTableName()
    {
        return (new UserGroup)->getTable();
    }

    public function getCreateRules()
    {
        return [
            "user_id" => "required|exists:users,id",
            "group_id" => "required|exists:groups,id",
        ];
    }

    public function getUpdateRules()
    {
        return [
            "user_id" => "sometimes|exists:users,id",
            "group_id" => "sometimes|exists:groups,id",
        ];
    }

    /**
     * Get the validation message which will be used to return error message when validation happen
     */
    public function getValidationMessages()
    {
        $messages = parent::getValidationMessages();

        $newMessages = [
            "user_id.required" => __("User is required."),
            "group_id.required" => __("Group is required.")
        ];

        return array_merge($messages, $newMessages);
    }

    /**
     * Function to assign users into groups
     * @param  [array] $users [List of user]
     * @param  [array] $groups [List of group]
     * @return [CommonResponse]                     [Response]
     */
    public function assignUsersIntoGroups($users, $groups)
    {
        if(!$users || count($users) == 0 ) {
            return (new CommonResponse(400, [], __("Invalid user data")));
        }

        if(!$groups || count($groups) == 0 ) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        DB::beginTransaction();
        try {
            $result = [];

            foreach($users as $user) {
                foreach($groups as $group) {
                    $itemResult = [
                        "user" => $user->id,
                        "group" => $group->scrub_id,
                        "result" => false
                    ];
                    //check existed
                    $existed = UserGroup::where('group_id', $group->id)->where('user_id', $user->id)->first();
                    if($existed) {
                        $itemResult['result'] = true;
                        array_push($result, $itemResult);
                        continue;
                    }

                    //if not existed
                    $userGroupData = [
                        "user_id" => $user->id,
                        "group_id" => $group->id,
                    ];
                    $createResult = $this->create($userGroupData);
                    if($createResult->isSuccess()) {
                        $itemResult['result'] = true;
                        array_push($result, $itemResult);
                        continue;
                    } else {
                        DB::rollBack();
                        return (new CommonResponse(500, [], __("Couldnt assign users into groups. Process stop due to unexpected data")));
                    }
                }
            }

            DB::commit();
            return (new CommonResponse(200, $result));
        }
        catch(\Exception $e) {
            DB::rollBack();
            return (new CommonResponse(500, [], __("Unexpected data")));
        }
    }

    /**
     * Function to remove users from groups
     * @param  [array] $users [List of user]
     * @param  [array] $groups [List of group]
     * @return [CommonResponse]                     [Response]
     */
    public function unassignUsersIntoGroups($users, $groups)
    {
        if(!$users || count($users) == 0 ) {
            return (new CommonResponse(400, [], __("Invalid user data")));
        }

        if(!$groups || count($groups) == 0 ) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        DB::beginTransaction();
        try {
            $result = [];

            foreach($users as $user) {
                foreach($groups as $group) {
                    $itemResult = [
                        "user" => $user->id,
                        "group" => $group->scrub_id,
                        "result" => false
                    ];
                    //check existed
                    $existed = UserGroup::where('group_id', $group->id)->where('user_id', $user->id)->first();
                    if(!$existed) {
                        $itemResult['result'] = true;
                        array_push($result, $itemResult);
                        continue;
                    }

                    //if existed
                    $removeResult = $existed->remove();
                    if($removeResult->isSuccess()) {
                        $itemResult['result'] = true;
                        array_push($result, $itemResult);
                        continue;
                    } else {
                        DB::rollBack();
                        return (new CommonResponse(500, [], __("Couldnt unassign users from groups. Process stop due to unexpected data")));
                    }
                }
            }

            DB::commit();
            return (new CommonResponse(200, $result));
        }
        catch(\Exception $e) {
            DB::rollBack();
            return (new CommonResponse(500, [], __("Unexpected data")));
        }
    }

    /**
     * Function to assign user into group
     * @param  [array] $userScrubIds [List of user scrub ids]
     * @return [CommonResponse]                     [Response]
     */
    public function assign($userScrubIds)
    {
        if(!$this->group || !$this->client || $this->group->client_id != $this->client->id ) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        if(!$userScrubIds || count($userScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid permission data")));
        }

        DB::beginTransaction();
        try {
            $result = [];

            foreach($userScrubIds as $userScrubId) {
                $user = User::where('id', $userScrubId)->first();
                if(!$user) {
                    DB::rollBack();
                    $error = [];
                    $error[$userScrubId] = __("User contain invalid data");
                    return (new CommonResponse(400, $error, __("User contain invalid data")));
                }

                //check existed
                $existedRelation = UserGroup::where('group_id', $this->group->id)->where('user_id', $user->id)->first();
                if($existedRelation) {
                    //dont have to do anything
                    $result[$userScrubId] = true;
                    continue;
                }

                //create new
                $newData = [
                    "group_id" => $this->group->id,
                    "user_id" => $user->id
                ];
                $createDataResult = $this->create($newData);

                if($createDataResult->isSuccess()) {
                    $result[$userScrubId] = true;
                    continue;
                } else {
                    DB::rollBack();
                    $error = [];
                    $error[$userScrubId] = $createDataResult->message;
                    return (new CommonResponse($createDataResult->code, $error, $createDataResult->message));
                }
            }

            DB::commit();
            return (new CommonResponse(200, $result));
        }
        catch(\Exception $e) {
            DB::rollBack();
            return (new CommonResponse(500, [], __("Unexpected data")));
        }
    }

    /**
     * Function to remove users from group
     * @param  [array] $userScrubIds [List of user scrub ids]
     * @return [CommonResponse]                     [Response]
     */
    public function unassign($userScrubIds)
    {
        if(!$this->group || !$this->client || $this->group->client_id != $this->client->id ) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        if(!$userScrubIds || count($userScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid user data")));
        }

        DB::beginTransaction();
        try {
            $result = [];

            foreach($userScrubIds as $userScrubId) {
                $user = User::where('id', $userScrubId)->first();
                if(!$user) {
                    DB::rollBack();
                    $error = [];
                    $error[$userScrubId] = __("User contain invalid data");
                    return (new CommonResponse(400, $error, __("User contain invalid data")));
                }

                //check existed
                $existedRelation = UserGroup::where('group_id', $this->group->id)->where('user_id', $user->id)->first();
                if(!$existedRelation) {
                    DB::rollBack();
                    $error = [];
                    $error[$userScrubId] = __("User contain invalid data");
                    return (new CommonResponse(400, $error, __("User contain invalid data")));
                }

                //remove items
                $deleteError = $existedRelation->remove();
                if($deleteError) {
                    //there is error
                    DB::rollBack();
                    $error = [];
                    $error[$userScrubId] = $deleteError;
                    return (new CommonResponse(400, $error, $deleteError));
                } else {
                    $result[$userScrubId] = true;
                }
            }

            DB::commit();
            return (new CommonResponse(200, $result));
        }
        catch(\Exception $e) {
            DB::rollBack();
            return (new CommonResponse(500, [], __("Unexpected data")));
        }
    }
}