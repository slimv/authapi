<?php
namespace App\Auth\Model;

use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use Illuminate\Support\Facades\Log;
use App\Model\PassportModel\PassportClient;
use App\Model\User;

class MyAccessToken extends PassportAccessToken {
    public function convertToJWT(CryptKey $privateKey)
    {
        $clientId = $this->getClient()->getIdentifier();

        //first we need to check client data first
        $client = PassportClient::find($clientId);
        if(!$client) {
            abort(400, 'Invalid token data');
        }

        if($client->password_client == 1) {
            //this is password login grant
            return (new Builder())
                ->setAudience($this->getClient()->getIdentifier())
                ->setId($this->getIdentifier(), true)
                ->setIssuedAt(time())
                ->setNotBefore(time())
                ->setExpiration($this->getExpiryDateTime()->getTimestamp())
                ->setSubject($this->getUserIdentifier())
                ->set('scopes', $this->getScopes())
                ->set('permissions', $this->getUserPermissions($this->getUserIdentifier(), $client))
                ->sign(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()))
                ->getToken();   
        } else {
            //this is login not using password (mostly client credential grant)
            return (new Builder())
                ->setAudience($this->getClient()->getIdentifier())
                ->setId($this->getIdentifier(), true)
                ->setIssuedAt(time())
                ->setNotBefore(time())
                ->setExpiration($this->getExpiryDateTime()->getTimestamp())
                ->setSubject($this->getUserIdentifier())
                ->set('scopes', $this->getScopes())
                ->sign(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()))
                ->getToken();
        }
    }

    public function getUserPermissions($userId, $client) {
        if(!$userId || !$client) {
            abort(400, 'Invalid token data');
        }
        $user = User::find($userId);
        if(!$user || !$client) {
            abort(400, 'Invalid token data');
        }

        $permissions = $user->getUserPermissionInClient($client);
        return $permissions;
    }
}