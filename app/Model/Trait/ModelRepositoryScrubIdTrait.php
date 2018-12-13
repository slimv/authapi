<?php
/**
 * This trait will provide data-relate support function which relate to scrub id.
 * Note: this trailt require function such as getModel available (Mostly belong to Repository)
 */

namespace App\Model\Traits;
use DB;
use Illuminate\Support\Facades\Log;
use App\Model\CommonResponse;

trait ModelRepositoryScrubIdTrait
{
    public $modelScrubIdColumnName = 'scrub_id';

    /**
     * Get the model by scrub value
     * @param  [string] $scrub [Scrub id]
     * @return [Model]        [Return model, or null if not found]
     */
    public function getByScrubId($scrub) {
        if(!$scrub) {
            return null;
        }

        $model = $this->getStateModel();
        $result = $model->where($this->modelScrubIdColumnName, $scrub)->first();
        return $result;
    }

    /**
     * Function to get model objects which have scrub id in selected list
     * @param  [array] $scrubs [List of scrub ids]
     * @param  [boolean] $allowOneNotFound [If true, even if one object is not found, it will still return the list. Otherwise it will return failed if one item is not found]
     * @return [type]     [description]
     */
    public function getScrubIdIn($scrubs, $allowOneNotFound = false)
    {
        try{
            if(!$scrubs){
                return (new CommonResponse(400, [], __("Invalid data")));
            }

            $model = $this->getStateModel();
            $rows = $model->whereIn($this->modelScrubIdColumnName, $scrubs)->get();

            if($allowOneNotFound) {
                return (new CommonResponse(200, $rows));
            } else {
                if(count($rows) != count($scrubs)) {
                    return (new CommonResponse(400, [], __("Contain invalid id")));
                } else {
                    return (new CommonResponse(200, $rows));
                }
            }

            return $result;
        }
        catch(Exception $e){
            return (new CommonResponse(500, [], __("Unexpect error")));
        }
    }
}