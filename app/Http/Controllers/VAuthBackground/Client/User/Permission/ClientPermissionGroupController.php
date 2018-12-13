<?php

namespace App\Http\Controllers\VAuthBackground\Client\User\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\ClientGrant\Permission\ClientPermissionGroupService;

class ClientPermissionGroupController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Client Permission Group Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle the process of assign/unassign user into group of selected client
    | NOTE: These api are only be used by another client, not by user. This is background api which normal user cannot have access to
    | NOTE: DO NOT CALL THIS API FROM FRONTEND OR USER APP. ONLY CALL IT FROM APPLICATION WHICH ADMIN USE
    |
    */
    protected $clientPermissionGroupService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ClientPermissionGroupService $clientPermissionGroupService)
    {
        $this->clientPermissionGroupService = $clientPermissionGroupService;
    }

    public function assignUserIntoGroups($clientId, $userId, Request $request)
    {
        $requestData = $request->only('groupKeys');
        $groupKeys = json_decode($requestData['groupKeys']);
        $clientResult = $this->clientPermissionGroupService->setClient($clientId);

        if(!$clientResult) {
            return (new CommonResponse(400, [], __("Invalid client data")))->toMyResponse();
        }

        return $this->clientPermissionGroupService->assignUsersToGroups([$userId], $groupKeys)->toMyResponse();
    }
}
