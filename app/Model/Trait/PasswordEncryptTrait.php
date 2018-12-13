<?php
/**
 * This trait will provide function to encrypt and decrypt password relate
 */

namespace App\Model\Traits;
use DB;
use Illuminate\Support\Facades\Log;

trait PasswordEncryptTrait
{
    /**
     * Function to crypt password to be used for user
     * @param  [string] $password [Origin password]
     * @return [string]           [Password after encrypted]
     */
    public function cryptUserModelPassword($password)
    {
        if(!$password) {
            return null;
        }

        return bcrypt($password);
    }
}