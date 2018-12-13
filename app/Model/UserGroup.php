<?php 
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\CoreModel;
use App\Model\Traits\ModelNormalDeleteTrait;

class UserGroup extends CoreModel
{
    use ModelNormalDeleteTrait;
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_group';

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = ['id', 'group', 'user', 'user_id', 'group_id'];

    protected $appends = ['group_data', 'user_data'];

    /**
     * Get this group
     */
    public function group()
    {
        return $this->belongsTo('App\Model\Group', 'group_id');
    }

    /**
     * Get this user
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User', 'user_id');
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
    public function getUserDataAttribute()
    {
        return $this->user;
    }
}