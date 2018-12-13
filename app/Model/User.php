<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Webpatser\Uuid\Uuid;
use App\Model\Group;
use App\Model\UserGroup;
use Illuminate\Support\Facades\Log;
use VDateTime;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Traits\ModelSoftDeleteTrait;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, ModelSoftDeleteTrait;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * Attach to the 'creating' Model Event to provide a UUID
         * for the `id` field (provided by $model->getKeyName())
         */
        static::creating(function ($model) {
            $model->incrementing = false;
            $model->id = (string)Uuid::generate();
        });
        static::retrieved(function ($model) {
            $model->incrementing = false;
        });
    }

    public function getPublicIdentity()
    {
        return $this->id;
    }

    /**
     * Get user permission in selected client
     * @param  [PassportClient]  $client             [The selected Client]
     * @param  boolean $permissionRequired [If true, this function will check for permission to view permission list. This case is used if this function is used in api call from outsite. If not, no permission require, work great for internal call]
     * @return [string[]]                      [list of permission in string]
     */
    public function getUserPermissionInClient($client, $permissionRequired = false)
    {
        if(!$client) {
            throw new \Exception('Invalid client data');
            return null;
        }

        if($permissionRequired) {
            return [];
        }

        //get all group this user belong to in this client
        $groups = Group::where(function($query) use ($client){
            $userGroups = UserGroup::select('group_id')
                ->where('user_id', $this->id)
                ->get();
            $groupIds = $userGroups->pluck('group_id')->toArray();
            $query = $query->where('client_id', $client->id);
            $query = $query->whereIn('id', $groupIds);
        })->get();

        $permissions = [];

        foreach($groups as $group) {
            $groupPermissions = $group->getAllPermissions();
            $groupPermissionKeys = $groupPermissions->pluck('key')->toArray();
            $permissions = array_unique(array_merge($permissions, $groupPermissionKeys));
        }

        return $permissions;
    }

    /**
     * Set the user as actived
     * @return [boolean] [save result]
     */
    public function activeUser()
    {
        $this->status = 'actived';
        return $this->save();
    }

    /**
     * Save the current time as last access time
     * @return [boolean] [save result]
     */
    public function registerLastAccess()
    {
        $this->last_access_at = VDateTime::now();
        return $this->save();
    }
}
