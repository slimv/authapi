<?php 
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\CoreModel;
use App\Model\Traits\ModelNormalDeleteTrait;

class GroupPermission extends CoreModel
{
    use ModelNormalDeleteTrait;
    
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group_permission';

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = ['id', 'group_id', 'permission_id', 'group', 'permission'];

    protected $appends = ['group_data', 'permission_data'];

    /**
     * Get this group
     */
    public function group()
    {
        return $this->belongsTo('App\Model\Group', 'group_id');
    }

    /**
     * Get this permission
     */
    public function permission()
    {
        return $this->belongsTo('App\Model\Permission', 'permission_id');
    }

    /**
     * Get this Group Participant
     *
     * @return text
     */
    public function getGroupDataAttribute()
    {
        return [
            "scrub_id" => $this->group->scrub_id,
            "name" => $this->group->name
        ];
    }

    /**
     * Get this Group Participant
     *
     * @return text
     */
    public function getPermissionDataAttribute()
    {
        return $this->permission;
    }
}