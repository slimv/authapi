<?php
/**
 * This trait will provide function to set repository deleted-view-ability
 * Note: this trailt require JwtSessionTrait to work
 */

namespace App\Model\Traits;
use DB;
use Illuminate\Support\Facades\Log;
use App\Model\CommonResponse;

trait ModelServiceSoftDeleteTrait
{
    public $modelMainSoftDeleteRepository = null;
    public $modelPermissionToViewDeletedName = null;

    /**
     * This function will set the main repository deleted-view-ability base on current jwt user
     * This function will be called by JwtSessionTrait function setJwtSessionFromRequest()
     */
    public function reloadMainRepositorySoftDeleteView() {
        if(!$this->modelMainSoftDeleteRepository || !$this->modelPermissionToViewDeletedName) {
            return null;
        }

        //enable deleted item if have permission
        if ($this->jwtCan([$this->modelPermissionToViewDeletedName])) {
            $this->modelMainSoftDeleteRepository->allowViewSoftDeleted();
        }
    }
}