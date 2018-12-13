<?php
/**
 * Permission Group Repository. This class will handle all the work relate to Permission Group
 * Note: Permission and Group work depend on client so make sure to set the client property
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Model\Repository\CoreRepository;
use App\Model\PassportModel\PassportClient;
use App\Model\Permission;
use App\Model\Group;
use App\Model\GroupPermission;
use Illuminate\Support\Facades\Log;
use App\Model\Traits\ModelRepositoryScrubIdTrait;

class ClientPermissionGroupRepository extends CoreRepository
{
    use ModelPropertyRestrictionTrait, ModelRepositoryScrubIdTrait;

    protected $all_search_fields = ['name', 'description', 'key'];
    public $allowQueryFields = ['name', 'description', 'parent_id', 'key'];

    public $client = null;

    /**
     * 
     */
    public function __construct()
    {
        $this->restrictFromRequestProperties = ['id', 'scrub_id', 'client_id', 'created_at', 'updated_at', 'deleted_at'];
    }

    public function getModel($origin = false)
    {
        $model = new Group;
        if($origin) {
            return $model;
        }
        $model = $model->where('client_id', $this->client->id);

        return $model;
    }

    public function getTableName()
    {
        return (new Group)->getTable();
    }

    public function getCreateRules()
    {
        return [
            "name" => "required|string",
            "key" => "required|string",
            "client_id" => 'nullable|exists:oauth_clients,id',
            "parent_id" => 'nullable|exists:groups,id',
            "description" => 'nullable|string'
        ];
    }

    public function getUpdateRules()
    {
        return [
            "name" => "sometimes|string",
            "key" => "sometimes|string",
            "client_id" => 'nullable|exists:oauth_clients,id',
            "parent_id" => 'nullable|exists:groups,id',
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
            "name.required" => __("Group name is required."),
            "key.required" => __("Group key is required."),
            "client_id.exists" => __("Invalid client data."),
            "parent_id.exists" => __("Invalid parent data.")
        ];

        return array_merge($messages, $newMessages);
    }

    /**
     * Get permission by name
     * @param  [string] $name [Permission group name]
     * @return [Group]      [Permission Group object]
     */
    public function getGroupByName($name)
    {
        if(!$name) {
            return null;
        }

        $model = $this->getModel();
        return $model->where('name', $name)->first();
    }

    /**
    * Get permission by key
    * @param  [string] $key [Permission group key]
    * @return [Group]      [Permission Group object]
    */
    public function getGroupByKey($key)
    {
        if(!$key) {
            return null;
        }

        $model = $this->getModel();
        return $model->where('key', $key)->first();
    }
}