<?php
/**
 * Client Repository. This class will handle all the work relate to Clients
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Model\Repository\CoreRepository;
use App\Model\PassportModel\PassportClient;
use Illuminate\Support\Facades\Log;

class ClientRepository extends CoreRepository
{
    use ModelPropertyRestrictionTrait;

    protected $all_search_fields = ['name'];
    public $allowQueryFields = ['name'];

    /**
     * 
     */
    public function __construct()
    {
        $this->restrictFromRequestProperties = ['id', 'secret', 'user_id', 'created_at', 'updated_at', 'deleted_at','revoked', 'personal_access_client', 'password_client'];
    }

    public function getModel($origin = false)
    {
        return new PassportClient;
    }

    public function getTableName()
    {
        return (new PassportClient)->getTable();
    }

    public function getPublicIdentity()
    {
        return $this->id;
    }

    public function getCreateRules()
    {
        return [
            "name" => "required|string|unique:oauth_clients",
            "user_id" => 'nullable|exists:users,id',
            "secret" => 'required|string|min:40|max:100',
            "personal_access_client" => 'sometimes|boolean',
            "password_client" => 'sometimes|boolean',
            "revoked" => 'sometimes|boolean',
            "redirect" => 'nullable|string'
        ];
    }

    public function getUpdateRules()
    {
        return [
            "name" => "sometimes|string|unique:oauth_clients",
            "user_id" => 'sometimes|exists:users,id',
            "secret" => 'sometimes|string|min:40|max:100',
            "personal_access_client" => 'sometimes|boolean',
            "password_client" => 'sometimes|boolean',
            "revoked" => 'sometimes|boolean',
            "redirect" => 'sometimes|string'
        ];
    }

    /**
     * Get the validation message which will be used to return error message when validation happen
     */
    public function getValidationMessages()
    {
        $messages = parent::getValidationMessages();

        $newMessages = [
            "name.required" => __("Client name is required."),
            "name.unique" => __("This name have been used by another client."),
            "secret.min" => __("Invalid secret length"),
            "secret.max" => __("Invalid secret length"),
        ];

        return array_merge($messages, $newMessages);
    }
}