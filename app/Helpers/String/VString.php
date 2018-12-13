<?php
namespace App\Helpers\String;

use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use VDateTime;

class VString {
    /**
     * Function to remove all space from string
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public function stripAllSpace($string)
    {
        return preg_replace('/\s+/', '', $string);
    }
}