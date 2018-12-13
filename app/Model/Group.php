<?php 
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\CoreModel;
use App\Model\Traits\ClosureTableModelTrait;
use App\Model\Permission;
use App\Model\GroupPermission;
use App\Model\Traits\ModelSoftDeleteTrait;

class Group extends CoreModel
{
    use SoftDeletes, ClosureTableModelTrait, ModelSoftDeleteTrait;

	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups';

    protected $dispatchesEvents = [
        'created' => \App\Event\Events\Database\Model\Group\GroupCreated::class,
        'deleted' => \App\Event\Events\Database\Model\Group\GroupDeleted::class,
        'deleting' => \App\Event\Events\Database\Model\Group\GroupDeleting::class,
        'updated' => \App\Event\Events\Database\Model\Group\GroupUpdated::class,
        'restored' => \App\Event\Events\Database\Model\Group\GroupRestored::class,
    ];

    //disable ranking in closure trait
    protected $disableClosureRank = true;

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['total_children'];

    public function getTotalChildrenAttribute()
    {
        return $this->getClosureTotalChildren();
    }

    /**
     * ClosureTableModelTrait require this function to work
     * @return [type] [description]
     */
    public function getClosureModel()
    {
        return new Group;
    }
    
    /**
     * Function to get this group permissions
     * Note: this function only return the permission for this group only, ignore its child
     * @param  boolean $permissionRequired [If true, this function will check for permission to view permission list. This case is used if this function is used in api call from outsite. If not, no permission require, work great for internal call]
     * @return [Permission[]]                      [List of permission of this group]
     */
    public function getPermissions($permissionRequired = false)
    {
        if($permissionRequired) {
            return [];
        }

        $permissions = Permission::where(function($query){
            $groupPermissions = GroupPermission::select('permission_id')
                ->where('group_id', $this->id)
                ->get();
            $permissionIds = $groupPermissions->pluck('permission_id')->toArray();
            $query = $query->whereIn('id', $permissionIds);
            $query = $query->where('client_id', $this->client_id);
        })->get();

        return $permissions;
    }

    /**
     * Function to get all of group permissions
     * Note: this function will go through the children list to get its permission and merge it into one array
     * @param  boolean $permissionRequired [If true, this function will check for permission to view permission list. This case is used if this function is used in api call from outsite. If not, no permission require, work great for internal call]
     * @return [Permission[]]                      [List of permission of this group]
     */
    public function getAllPermissions($permissionRequired = false)
    {
        if($permissionRequired) {
            return [];
        }

        $groupFamilyIds = $this->getClosureTreeIdList();

        $permissions = Permission::where(function($query) use ($groupFamilyIds){
            $groupPermissions = GroupPermission::select('permission_id')
                ->whereIn('group_id', $groupFamilyIds)
                ->get();
            $permissionIds = $groupPermissions->pluck('permission_id')->toArray();
            $query = $query->whereIn('id', $permissionIds);
            $query = $query->where('client_id', $this->client_id);
        })->get();

        return $permissions;
    }
}