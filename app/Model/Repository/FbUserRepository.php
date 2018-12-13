<?php
/**
 * User Repository. This class will handle all the work relate to User and its table in database
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Model\Repository\CoreRepository;
use App\Model\User;

class FbUserRepository extends CoreRepository
{
    use ModelPropertyRestrictionTrait;

    protected $all_search_fields = ['email', 'first_name', 'last_name'];

    /**
     * 
     */
    public function __construct()
    {
        $this->restrictFromRequestProperties = ['id', 'last_access_at', 'created_at', 'updated_at', 'deleted_at'];
    }

    public function getModel($origin = false)
    {
        return new User;
    }

    public function getTableName()
    {
        return (new User)->getTable();
    }

    public function getCreateRules()
    {
        return [
            "email" => "required|email|unique:users",
            "first_name" => "required|string",
            "last_name" => "sometimes|string"
        ];
    }

    public function getUpdateRules()
    {
        $passwordMinLength = config('security.password.minlength');
        $passwordMaxLength = config('security.password.maxlength');
        $passwordRegex = config('security.password.validation_regex');
        $regexPasswordValidation = '';
        if($passwordRegex) {
            $regexPasswordValidation = "|regex:/" . $passwordRegex . '/';
        }

        $idUpdating = $this->getOldObjectUpdated()->id;
        return [
            "email" => "sometimes|email|unique:users,email," . $idUpdating,
            "first_name" => "sometimes|string",
            "last_name" => "sometimes|string",
            "origin_password" => "sometimes|string|min:".$passwordMinLength."|max:".$passwordMaxLength.$regexPasswordValidation
        ];
    }

    /**
     * Get the validation message which will be used to return error message when validation happen
     */
    public function getValidationMessages()
    {
        $messages = parent::getValidationMessages();

        $newMessages = [
            "email.unique" => __("This email already be used."),
            "origin_password.regex" => __(config('security.password.validation_message')),
        ];

        return array_merge($messages, $newMessages);
    }
}