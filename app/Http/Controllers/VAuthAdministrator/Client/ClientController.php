<?php

namespace App\Http\Controllers\VAuthAdministrator\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Client\ClientService;

class ClientController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Client Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to Client
    |
    */
    private $clientService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Api to get client detail
     * Permissions:
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function client($clientId, Request $request)
    {
        $requestData = $request->all();
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->fetchClient($clientId)->toMyResponse();
    }

    /**
     * Api to get list of client in the system
     * Permissions:
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function clients(Request $request)
    {
        $requestData = $request->all();
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->fetchAll($requestData)->toMyResponse();
    }

    /**
     * Api to create new client
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:create [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function createClient(Request $request)
    {
        $requestData = $request->only('name', 'redirect', 'client_type');
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->createClient($requestData)->toMyResponse();
    }

    /**
     * Api to update client
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:update [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function updateClient($clientScrubId, Request $request)
    {
        $requestData = $request->only('name', 'redirect');
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->updateClient($clientScrubId, $requestData)->toMyResponse();
    }

    /**
     * Api to update client secret
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:regenerate-secret [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function resetSecret($clientScrubId, Request $request)
    {
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->resetSecret($clientScrubId)->toMyResponse();
    }

    /**
     * Api to lock clients
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:lock [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function lockClients(Request $request)
    {
        $requestData = $request->only('ids');
        $clientIds = json_decode($requestData['ids']);
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->lockClients($clientIds)->toMyResponse();
    }

    /**
     * Api to unlock clients
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:unlock [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function unlockClients(Request $request)
    {
        $requestData = $request->only('ids');
        $clientIds = json_decode($requestData['ids']);
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->unlockClients($clientIds)->toMyResponse();
    }

    /**
     * Api to delete clients
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:delete [required]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function deleteClients(Request $request)
    {
        $requestData = $request->only('ids');
        $clientIds = json_decode($requestData['ids']);
        $this->clientService->setJwtSessionFromRequest($request);
        return $this->clientService->deleteClients($clientIds)->toMyResponse();
    }
}
