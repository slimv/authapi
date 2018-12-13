<?php
/**
 * User Repository. This class will handle all the work relate to User and its table in database
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Model\Repository\CoreRepository;
use App\Model\User;
use App\Model\Group;
use App\Model\UserGroup;

class ClientUserRepository extends CoreRepository
{
    use ModelPropertyRestrictionTrait;

    public $client;

    protected $all_search_fields = ['email', 'first_name', 'last_name'];

    /**
     * 
     */
    public function __construct()
    {
        $this->restrictFromRequestProperties = ['id', 'status', 'last_access_at', 'created_at', 'updated_at', 'deleted_at'];
    }

    public function getModel($origin = false)
    {
        $model = new User;
        if($origin) {
            return $model;
        }

        if($this->client) {
            //get all group belong to this client and check if user belong to these group or not (do not deleted group)
            $groups = Group::where('client_id', $this->client->id)->get();
            $groupIds = $groups->pluck('id')->toArray();

            //make sure client belong to there groups
            $model = $model->where(function($query) use ($groupIds){
                $userIds = UserGroup::whereIn('group_id', $groupIds)->get()->pluck('user_id')->toArray();
                $query = $query->whereIn('id', $userIds);
            });
        }

        return new User;
    }

    public function getTableName()
    {
        return (new User)->getTable();
    }

    public function getCreateRules()
    {
        return [];
    }

    public function getUpdateRules()
    {
        return [];
    }

    /**
     * Get the validation message which will be used to return error message when validation happen
     */
    public function getValidationMessages()
    {
        return [];
    }

    /**
     * Override this function to update items after fetching
     * @param  [type] $items [description]
     * @return [type]        [description]
     */
    public function parseItemsAfterFetch($items)
    {
        foreach($items as $user) {
            //get the group data
            $groups = Group::where('client_id', $this->client->id)->where(function($query) use ($user) {
                $groupIds = UserGroup::where('user_id', $user->id)->get()->pluck('group_id')->toArray();
                $query = $query->whereIn('id', $groupIds);
            })->get();

            $groupData = [];
            foreach($groups as $group) {
                array_push($groupData, [
                    "scrub_id" => $group->scrub_id,
                    "name" => $group->name,
                    "key" => $group->key
                ]);
            }

            $user['groups'] = $groupData;
        }

        return $items;
    }
}