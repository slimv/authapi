<?php
/**
 * User Active Code Repository. This class will handle the process of generating the code for user to be used in various case such as
 * - Signup
 * - Forgot password
 */
namespace App\Http\Model\Table;

use App\Model\Repository\CoreRepository;
use App\Model\User;
use App\Model\UserActiveCode;
use Illuminate\Validation\Rule;

class UserActiveCodeRepository extends CoreRepository
{

    protected $all_search_fields = ['code'];

    /**
     * 
     */
    public function __construct()
    {

    }

    public function getModel($origin = false)
    {
        return new UserActiveCode;
    }

    public function getTableName()
    {
        return (new UserActiveCode)->getTable();
    }

    public function getCreateRules()
    {
        return [
            "code" => "required|unique:user_active_codes",
            "user_id" => 'required|exists:users,id',
            "type" => [
                'required',
                Rule::in(['forgot-password', 'signup'])
            ],
            "status" => [
                'sometimes',
                Rule::in(['actived', 'ready'])
            ],
        ];
    }

    public function getUpdateRules()
    {
        return [
            "status" => [
                'sometimes',
                Rule::in(['actived', 'ready'])
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
            "code.unique" => __("This code already existed."),
            "type.in" => __("Invalid code type."),
            "status.in" => __("Invalid status value.")
        ];

        return array_merge($messages, $newMessages);
    }

    /**
     * Get code object from code string
     * Note: this function always return code even if it expired
     * @param  [string] $code [Selected code]
     * @param  [string] $type [Code type]
     * @return [UserActiveCode]       [The return code.]
     */
    public function getCode($code, $type)
    {
        if(!$code || !$type) {
            return null;
        }

        return UserActiveCode::where('code', $code)->where('type', $type)->first();
    }
}