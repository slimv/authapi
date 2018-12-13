<?php
/**
 *  --------------------------------------------------------------------------
 *   NOTE:
 *  --------------------------------------------------------------------------

 *   This class will be used to parse access token
 *
 */

namespace App\Auth\Providers;

use Firebase\JWT\JWT;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;
use Storage;

class PassportTokenParser
{
    protected $encrypter;

    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function parseToken($token)
    {
        $public_key = Storage::disk('storage_root')->get(config('passport.public_key_file'));

        $decrypt = (array) JWT::decode(
            $token,
            $public_key, 
            [config('passport.decode_algo')]
        );

        return $decrypt;
    }

}