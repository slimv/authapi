<?php
namespace App\Auth\Repository;

use App\Auth\Model\MyAccessToken; // AccessToken from step 1
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;

class MyAccessTokenRepository extends PassportAccessTokenRepository
{
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new MyAccessToken($userIdentifier, $scopes);
    }
}