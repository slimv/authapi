<?php
/**
 * This trait will provide support function relate to soft delete such as lock/unlock
 */

namespace App\Model\Traits;
use DB;
use Illuminate\Support\Facades\Log;

trait ModelNormalDeleteTrait
{
    /**
     * Completly remove the object, revert will be impossible
     * @param  [string] $reason [Reason why this got deleted]
     * @return [string|null] [null if success, string if error occurred]
     */
    public function remove($reason = null)
    {
        try {
            $this->delete();

            return null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}