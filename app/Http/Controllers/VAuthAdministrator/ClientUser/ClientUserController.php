<?php

namespace App\Http\Controllers\VAuthAdministrator\Client\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Client\ClientUserService;

class ClientUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Client Permission Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle all api relate to Client Permission
    |
    */
    private $clientUserService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ClientUserService $clientUserService)
    {
        $this->clientUserService = $clientUserService;
    }

    /**
     * Api to get list of users for selected client
     * Permissions:
     *  - client:view [required]
     *  - client:view-deleted [optional]
     *  - user:view [required]
     * @param  [string] $clientId [Selected client id]
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function users($clientId, Request $request)
    {
        $requestData = $request->all();
        $this->clientUserService->setJwtSessionFromRequest($request);
        $this->clientUserService->setClient($clientId);
        return $this->clientUserService->fetchAll($requestData)->toMyResponse();
    }
}
