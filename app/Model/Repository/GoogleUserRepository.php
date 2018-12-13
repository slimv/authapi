<?php
/**
 * User Repository. This class will handle all the work relate to User and its table in database
 */
namespace App\Http\Model\Table;

use App\Model\Traits\ModelPropertyRestrictionTrait;
use App\Http\Model\Table\FbUserRepository;
use App\Model\User;

class GoogleUserRepository extends FbUserRepository
{
    use ModelPropertyRestrictionTrait;
}