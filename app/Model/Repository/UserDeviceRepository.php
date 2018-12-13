<?php
/**
 * User Device Repository. This class will handle the process of managing user devices such as mobile, web browser
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Model\Repository\CoreRepository;
use App\Model\User;
use App\Model\UserDevice;
use Illuminate\Validation\Rule;

class UserDeviceRepository extends CoreRepository
{
    use ModelPropertyRestrictionTrait;

    public $user = null;                //if set, model will only return user

    protected $all_search_fields = ['device_type', 'device_name', 'device_id'];

    /**
     * 
     */
    public function __construct()
    {
        $this->restrictFromRequestProperties = ['id', 'status', 'last_access_at', 'created_at', 'updated_at'];
    }

    public function getModel($origin = false)
    {
        $model = new UserDevice;
        if($origin) {
            return $model;
        }
        
        if($this->user) {
            $model = $model->where('user_id', $this->user->id);
        }
        
        return $model;
    }

    public function getTableName()
    {
        return (new UserDevice)->getTable();
    }

    public function getCreateRules()
    {
        return [
            "device_id" => "required|unique:user_devices",
            "user_id" => 'required|exists:users,id',
            "device_name" => 'nullable|string',
            "device_type" => [
                'required',
                Rule::in(['ios', 'android', 'web-browser', 'win-phone'])
            ],
            "status" => [
                'sometimes',
                Rule::in(['actived', 'deactived'])
            ],
        ];
    }

    public function getUpdateRules()
    {
        return [
            "status" => [
                'sometimes',
                Rule::in(['actived', 'deactived'])
            ],
            "device_type" => [
                'sometimes',
                Rule::in(['ios', 'android', 'web-browser', 'win-phone'])
            ],
            "user_id" => 'sometimes|exists:users,id',
        ];
    }

    /**
     * Get the validation message which will be used to return error message when validation happen
     */
    public function getValidationMessages()
    {
        $messages = parent::getValidationMessages();

        $newMessages = [
            "device_id.unique" => __("This device have already be registed."),
            "type.in" => __("Invalid code type."),
            "device_type.in" => __("Invalid device type."),
            "status.in" => __("Invalid status value.")
        ];

        return array_merge($messages, $newMessages);
    }
}