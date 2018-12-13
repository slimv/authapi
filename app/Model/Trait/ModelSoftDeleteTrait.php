<?php
/**
 * This trait will provide support function relate to soft delete such as lock/unlock
 */

namespace App\Model\Traits;
use DB;
use Illuminate\Support\Facades\Log;

trait ModelSoftDeleteTrait
{
    /**
     * Lock model
     * @param  [string] $reason [Reason why this got lock]
     * @return [string|null] [null if success, string if error occurred]
     */
    public function lock($reason = null)
    {
        try {
            $this->delete();
            return null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Unlock model
     * @param  [string] $reason [Reason why this got unlock]
     * @return [string|null] [null if success, string if error occurred]
     */
    public function unlock($reason = null)
    {
        try {
            $result = $this->restore();
            return null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Completly remove the object, revert will be impossible
     * @param  [string] $reason [Reason why this got deleted]
     * @return [string|null] [null if success, string if error occurred]
     */
    public function remove($reason = null)
    {
        try {
            $this->forceDelete();

            return null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}