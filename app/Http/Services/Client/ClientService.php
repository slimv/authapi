<?php
/**
 * Service to handle client (private only)
 */

namespace App\Http\Service\Client;

use App\Http\Model\Table\ClientRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\PassportMode\PassportClient;
use VDateTime;
use VStringGenerator;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;
use App\Model\Traits\ModelServiceSoftDeleteTrait;
use Illuminate\Support\Facades\Log;

class ClientService
{
    use JwtSessionTrait, PermissionTrait, ModelServiceSoftDeleteTrait;

    /** @var ClientRepository */
    private $clientRepo;

    /**
     * AuthService constructor.
     * @param ClientRepository $clientRepo
     */
    public function __construct(ClientRepository $clientRepo)
    {
        $this->clientRepo = $clientRepo;

        //ModelServiceSoftDeleteTrait properties
        $this->modelMainSoftDeleteRepository = $this->clientRepo;
        $this->modelPermissionToViewDeletedName = 'client:view-deleted';
    }

    /**
     * Function to get a client using its id
     * @param  [string] $clientId [Client id]
     * @return [CommonResponse]           [Response]
     */
    public function fetchClient($clientId)
    {
        if(!$clientId) {
            return new CommonResponse(400, [], __("Invalid client information"));
        }
        
        $client = $this->clientRepo->find($clientId);

        if(!$client) {
            return new CommonResponse(400, [], __("Invalid client information"));
        }

        return new CommonResponse(200, $client);
    }

    /**
     * Function to query clients.
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function fetchAll($query)
    {
        $clients = $this->clientRepo->items($query);

        return new CommonResponse(200, $clients);
    }

    /**
     * Function to create new client
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:create [required]
     * @param  [array] $clientData [Data of client to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function createClient($clientData) 
    {
        if(!$clientData) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }
        
        if(!isset($clientData['client_type']) || 
            ( $clientData['client_type'] != "password_grant" &&
            $clientData['client_type'] != "client_credential_grant" &&
            $clientData['client_type'] != "authorization_code_grant" )) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //first we need to remove uneccessary data
        $clientData = $this->clientRepo->stripPropertiesFromRequestData($clientData);
        $clientData['user_id'] = $user->id;
        $clientData['secret'] = VStringGenerator::randomString(50);

        if($clientData['client_type'] == "password_grant") {
            $clientData['personal_access_client'] = 0;
            $clientData['password_client'] = 1;
        } else if($clientData['client_type'] == "client_credential_grant") {
            $clientData['personal_access_client'] = 0;
            $clientData['password_client'] = 0;
        } else if($clientData['client_type'] == "authorization_code_grant") {
            $clientData['personal_access_client'] = 0;
            $clientData['password_client'] = 0;
        }

        //now we create the client
        $createResult = $this->clientRepo->create($clientData);

        return $createResult;
    }

    /**
     * Function to update client with given data
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  [array] $clientData [Data of client to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function updateClient($clientScrubId, $updateData) 
    {
        if(!$updateData || !$clientScrubId) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $client = $this->clientRepo->find($clientScrubId);
        if(!$client) {
            return (new CommonResponse(400, [], __("Invalid client data")));
        }

        //first we need to remove uneccessary data
        $updateData = $this->clientRepo->stripPropertiesFromRequestData($updateData);

        //now we update the client
        $updateResult = $this->clientRepo->update($client, $updateData);

        return $updateResult;
    }

    /**
     * Function to reset secret of a client
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:regenerate-secret [required]
     * @param  [array] $clientData [Data of client to be created]
     * @return [CommonResponse]       [Generated result]
     */
    public function resetSecret($clientScrubId) 
    {
        if(!$clientScrubId) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = $this->jwtCurrentUser;
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $client = $this->clientRepo->find($clientScrubId);
        if(!$client) {
            return (new CommonResponse(400, [], __("Invalid client data")));
        }

        //generate secret data
        $updateData = [];
        $updateData['secret'] = VStringGenerator::randomString(50);

        //now we update the client
        $updateResult = $this->clientRepo->update($client, $updateData);

        return $updateResult;
    }

    /**
     * Function to lock clients
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:lock [required]
     * @param  [array] $clientScrubIds [List of client ids need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function lockClients($clientIds)
    {
        if(!$clientIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $lookUpResult = $this->clientRepo->findIn($clientIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $clients = $lookUpResult->data;

        //now we update the client
        $lockResult = $this->clientRepo->delete($clients, false, true);
        return $lockResult;
    }

    /**
     * Function to unlock clients
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:unlock [required]
     *  - client:view-deleted [required]
     * @param  [array] $clientScrubIds [List of client ids need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function unlockClients($clientIds)
    {
        if(!$clientIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $this->clientRepo->allowViewSoftDeleted();

        $lookUpResult = $this->clientRepo->findIn($clientIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $clients = $lookUpResult->data;

        //now we update the client
        $lockResult = $this->clientRepo->recovery($clients, true);
        return $lockResult;
    }

    /**
     * Function to delete clients
     * Note: this function is only be used by Vauth control panel system only, do not allow public user to use this
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:delete [required]
     * @param  [array] $clientScrubIds [List of client ids need to be locked]
     * @return [CommonResponse]       [Generated result]
     */
    public function deleteClients($clientIds)
    {
        if(!$clientIds) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $lookUpResult = $this->clientRepo->findIn($clientIds);
        if(!$lookUpResult->isSuccess()) {
            return $lookUpResult;
        }

        $clients = $lookUpResult->data;

        //now we delete the client
        $lockResult = $this->clientRepo->delete($clients, true, true);
        return $lockResult;
    }
}