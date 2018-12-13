<?php
/**
 * Permission Repository. This class will handle all the work relate to Permission
 * Note: Permission work depend on client so make sure to set the client property
 */
namespace App\Http\Model\Table;

use App\Model\Repository\CoreRepository;
use App\Model\PassportModel\PassportClient;
use App\Model\Permission;
use App\Model\GroupPermission;
use Illuminate\Support\Facades\Log;
use App\Model\Traits\ModelRepositoryScrubIdTrait;
use App\Model\CommonResponse;
use DB;

class ClientPermissionGroupMidRepository extends CoreRepository
{
    use ModelRepositoryScrubIdTrait;

    public $client = null;
    public $group = null;               //when set, the assign function will target this group
    public $enableClosure = false;      //if true, when filter with group will check for its closure

    /**
     * 
     */
    public function __construct()
    {
        
    }

    public function getModel($origin = false)
    {
        $model = new GroupPermission;
        if($origin) {
            return $model;
        }

        $model = $model->join('permissions', 'permission_id', '=', 'permissions.id');

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
        return (new GroupPermission)->getTable();
    }

    public function getCreateRules()
    {
        return [
            "permission_id" => "required|exists:permissions,id",
            "group_id" => "required|exists:groups,id",
        ];
    }

    public function getUpdateRules()
    {
        return [
            "permission_id" => "sometimes|exists:permissions,id",
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
            "permission_id.required" => __("Permission is required."),
            "group_id.required" => __("Group is required.")
        ];

        return array_merge($messages, $newMessages);
    }

    /**
     * Function to assign permission into group
     * @param  [array] $permissionScrubIds [List of permission scrub ids]
     * @return [CommonResponse]                     [Response]
     */
    public function assign($permissionScrubIds)
    {
        if(!$this->group || !$this->client || $this->group->client_id != $this->client->id ) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        if(!$permissionScrubIds || count($permissionScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid permission data")));
        }

        DB::beginTransaction();
        try {
            $result = [];

            foreach($permissionScrubIds as $permissionScrubId) {
                $permission = Permission::where('scrub_id', $permissionScrubId)->where('client_id', $this->client->id)->first();
                if(!$permission) {
                    DB::rollBack();
                    $error = [];
                    $error[$permissionScrubId] = __("Permission contain invalid data");
                    return (new CommonResponse(400, $error, __("Permission contain invalid data")));
                }

                //check existed
                $existedRelation = GroupPermission::where('group_id', $this->group->id)->where('permission_id', $permission->id)->first();
                if($existedRelation) {
                    //dont have to do anything
                    $result[$permissionScrubId] = true;
                    continue;
                }

                //create new
                $newData = [
                    "group_id" => $this->group->id,
                    "permission_id" => $permission->id
                ];
                $createDataResult = $this->create($newData);

                if($createDataResult->isSuccess()) {
                    $result[$permissionScrubId] = true;
                    continue;
                } else {
                    DB::rollBack();
                    $error = [];
                    $error[$permissionScrubId] = $createDataResult->message;
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
     * Function to remove permissions from group
     * @param  [array] $permissionScrubIds [List of permission scrub ids]
     * @return [CommonResponse]                     [Response]
     */
    public function unassign($permissionScrubIds)
    {
        if(!$this->group || !$this->client || $this->group->client_id != $this->client->id ) {
            return (new CommonResponse(400, [], __("Invalid group data")));
        }

        if(!$permissionScrubIds || count($permissionScrubIds) == 0) {
            return (new CommonResponse(400, [], __("Invalid permission data")));
        }

        DB::beginTransaction();
        try {
            $result = [];

            foreach($permissionScrubIds as $permissionScrubId) {
                $permission = Permission::where('scrub_id', $permissionScrubId)->where('client_id', $this->client->id)->first();
                if(!$permission) {
                    DB::rollBack();
                    $error = [];
                    $error[$permissionScrubId] = __("Permission contain invalid data");
                    return (new CommonResponse(400, $error, __("Permission contain invalid data")));
                }

                //check existed
                $existedRelation = GroupPermission::where('group_id', $this->group->id)->where('permission_id', $permission->id)->first();
                if(!$existedRelation) {
                    DB::rollBack();
                    $error = [];
                    $error[$permissionScrubId] = __("Permission contain invalid data");
                    return (new CommonResponse(400, $error, __("Permission contain invalid data")));
                }

                //remove items
                $deleteError = $existedRelation->remove();
                if($deleteError) {
                    //there is error
                    DB::rollBack();
                    $error = [];
                    $error[$permissionScrubId] = $deleteError;
                    return (new CommonResponse(400, $error, $deleteError));
                } else {
                    $result[$permissionScrubId] = true;
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