<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport setting
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for passport setting
    |
    */

    'decode_algo' => 'RS256',
    'public_key_file' => 'oauth-public.key',                            //path to jwt public, root from storage/
    'private_key_file' => 'oauth-private.key'                           //path to jwt public, root from storage/

];
