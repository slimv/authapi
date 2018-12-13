<?php
/**
 * Permission Repository. This class will handle all the work relate to Permission
 * Note: Permission work depend on client so make sure to set the client property
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Model\Repository\CoreRepository;
use App\Model\PassportModel\PassportClient;
use App\Model\User;
use App\Model\UserGroup;
use Illuminate\Support\Facades\Log;
use App\Model\Traits\ModelRepositoryScrubIdTrait;
use App\Http\Model\Table\ClientPermissionRepository;

class ClientAvailableUserGroupRepository extends ClientPermissionRepository
{
    use ModelPropertyRestrictionTrait, ModelRepositoryScrubIdTrait;

    public $targetGroup = null;                     //if set, we will only get permission which not belong to this group

    /**
     * 
     */
    public function __construct()
    {
        $this->restrictFromRequestProperties = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $this->order_by = 'email';
        $this->order_dir = 'asc';
    }

    public function getModel($origin = false)
    {
        $model = new User;
        if($origin) {
            return $model;
        }

        if($this->targetGroup) {
            //only get users which havent been assign to this group or its chidrent yet
            if(method_exists($this->targetGroup, 'getClosureTreeIdList')) {
                $groupFamilyIds = $this->targetGroup->getClosureTreeIdList();
                $groupUsers = UserGroup::whereIn('group_id', $groupFamilyIds)->get();
                $userIds = $groupUsers->pluck('user_id')->toArray();
                $model = $model->whereNotIn('id', $userIds);
            }
        }

        return $model;
    }

    public function getTableName()
    {
        return (new User)->getTable();
    }
}