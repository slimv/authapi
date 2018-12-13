<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security setting
    |--------------------------------------------------------------------------
    |
    | This file is for storing the setting for this system security
    |
    */

    'password' => [
        'minlength' => 8,
        'maxlength' => 30,
        'validation_regex' => (env('SECURITY_PASSWORD_LEVEL') == 'high' ? '^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,30}$' : null),
        'validation_message' => 'Password must be at least 8 characters, no more than 30 characters, and must include at least one upper case letter, one lower case letter, and one numeric digit',
    ],

];
