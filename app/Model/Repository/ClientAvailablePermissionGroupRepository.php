<?php
/**
 * Permission Repository. This class will handle all the work relate to Permission
 * Note: Permission work depend on client so make sure to set the client property
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Model\Repository\CoreRepository;
use App\Model\PassportModel\PassportClient;
use App\Model\Permission;
use App\Model\GroupPermission;
use Illuminate\Support\Facades\Log;
use App\Model\Traits\ModelRepositoryScrubIdTrait;
use App\Http\Model\Table\ClientPermissionRepository;

class ClientAvailablePermissionGroupRepository extends ClientPermissionRepository
{
    use ModelPropertyRestrictionTrait, ModelRepositoryScrubIdTrait;

    public $targetGroup = null;                     //if set, we will only get permission which not belong to this group

    public function getModel($origin = false)
    {
        $model = new Permission;
        if($origin) {
            return $model;
        }
        $model = $model->where('client_id', $this->client->id);

        if($this->targetGroup) {
            //only get permission which havent been assign to this group or its chidrent yet
            if(method_exists($this->targetGroup, 'getClosureTreeIdList')) {
                $groupFamilyIds = $this->targetGroup->getClosureTreeIdList();
                $groupPermissions = GroupPermission::whereIn('group_id', $groupFamilyIds)->get();
                $permissionIds = $groupPermissions->pluck('permission_id')->toArray();
                $model = $model->whereNotIn('id', $permissionIds);
            }
        }

        return $model;
    }
}