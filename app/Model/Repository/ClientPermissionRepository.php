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

class ClientPermissionRepository extends CoreRepository
{
    use ModelPropertyRestrictionTrait, ModelRepositoryScrubIdTrait;

    protected $all_search_fields = ['key', 'description'];
    public $allowQueryFields = ['key', 'description'];

    public $client = null;
    public $targetGroup = null;                     //if set, we will only get permission which belong to this group family tree)

    /**
     * 
     */
    public function __construct()
    {
        $this->restrictFromRequestProperties = ['id', 'scrub_id', 'client_id', 'created_at', 'updated_at', 'deleted_at'];
        $this->order_by = 'permissions.key';
        $this->order_dir = 'desc';
    }

    public function getModel($origin = false)
    {
        $model = new Permission;
        if($origin) {
            return $model;
        }
        $model = $model->where('client_id', $this->client->id);

        if($this->targetGroup) {
            //this will work for closure
            if(method_exists($this->targetGroup, 'getClosureTreeIdList')) {
                $groupFamilyIds = $this->targetGroup->getClosureTreeIdList();
                $groupPermissions = GroupPermission::whereIn('group_id', $groupFamilyIds)->get();
                $permissionIds = $groupPermissions->pluck('permission_id')->toArray();

                $model = $model->whereIn('id', $permissionIds);
            }
        }

        return $model;
    }

    public function getTableName()
    {
        return (new Permission)->getTable();
    }

    public function getCreateRules()
    {
        return [
            "key" => "required|string",
            "client_id" => 'nullable|exists:oauth_clients,id',
            "description" => 'nullable|string'
        ];
    }

    public function getUpdateRules()
    {
        return [
            "key" => "sometimes|string",
            "client_id" => 'nullable|exists:oauth_clients,id',
            "description" => 'nullable|string'
        ];
    }

    /**
     * Get the validation message which will be used to return error message when validation happen
     */
    public function getValidationMessages()
    {
        $messages = parent::getValidationMessages();

        $newMessages = [
            "key.required" => __("Permission key is required.")
        ];

        return array_merge($messages, $newMessages);
    }

    /**
     * Get permission by key
     * @param  [string] $key [Permission key]
     * @return [Permission]      [Permission object]
     */
    public function getPermissionByKey($key)
    {
        if(!$key) {
            return null;
        }

        $model = $this->getModel();
        return $model->where('key', $key)->first();
    }
}