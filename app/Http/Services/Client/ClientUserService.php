<?php
/**
 * Service to handle client user
 */

namespace App\Http\Service\Client;

use App\Http\Model\Table\ClientRepository;
use App\Http\Model\Table\UserRepository;
use App\Http\Model\Table\ClientUserRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\Permission;
use App\Model\PassportMode\PassportClient;
use VDateTime;
use VStringGenerator;
use App\Traits\Session\JwtSessionTrait;
use App\Traits\PermissionTrait;

class ClientUserService
{
    use JwtSessionTrait, PermissionTrait;

    private $clientRepo;
    private $userRepo;
    private $clientUserRepo;

    /**
     * ClientUserService constructor.
     */
    public function __construct(UserRepository $userRepo, ClientRepository $clientRepo, ClientUserRepository $clientUserRepo)
    {
        $this->userRepo = $userRepo;
        $this->clientRepo = $clientRepo;
        $this->clientUserRepo = $clientUserRepo;
    }

    /**
     * Set client using its ids
     * Always call this function after JWT session have been set
     * @param [string] $clientId [Client Id]
     */
    public function setClient($clientId)
    {
        if ($this->jwtCan(['client:view-deleted'])) {
            $this->clientRepo->allowViewSoftDeleted();
        }

        $client = $this->clientRepo->find($clientId);
        if($client) {
            $this->clientUserRepo->client = $client;
        }

        if ($this->jwtCan(['user:view-deleted'])) {
            $this->clientUserRepo->allowViewSoftDeleted();
        }
    }

    /**
     * Function to get all user in client
     * Note: this function will check for any user who belong to group which belong to client and return them
     * Permissions:
     *  - auth:access [required]
     *  - client:view [required]
     *  - client:view-deleted [optional]
     * @param  [array] $query [Query data]
     * @return [CommonResponse]        [Response]
     */
    public function fetchAll($query)
    {
        $permissions = $this->clientUserRepo->items($query);

        return new CommonResponse(200, $permissions);
    }
}