<?php
/**
 * This trait will provide restriction for Model Repository when trying to create or update model
 * NOTE: this trail is better using with repository, not recommend using this for other purpose
 */

namespace App\Model\Traits;
use DB;
use Illuminate\Support\Facades\Log;

trait ModelPropertyRestrictionTrait
{
    /**
     * There properties listed here wont be able to be updated from outsite request. For example, you wont want user
     * to update id field when they send request to update data and they include id value into the request.
     * @var array
     */
    public $restrictFromRequestProperties = [];

    /**
     * This function will strip restrict data from request data and return new data array which dont have those field
     * @param  [array] $data [Request data]
     * @return [array]       [Data after restricted properties are removed]
     */
    public function stripPropertiesFromRequestData($data) {
        if(!$data) {
            return null;
        }

        foreach($this->restrictFromRequestProperties as $property) {
            if(isset($data[$property])) {
                unset($data[$property]);
            }
        }

        return $data;
    }
}