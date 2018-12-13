<?php
/**
 * Helper for date
 */

namespace App\Helpers\Date;

use Carbon\Carbon;

class VDateTime
{
    public static function now()
    {
        $carbonNow = Carbon::now();

        return $carbonNow;
    }

    /**
     * Check if selected date is before now or not
     * @param  [Datetime]  $date [Selected date]
     * @return boolean       [true if before]
     */
    public static function isBeforeNow($date)
    {
    	$carbonDate = Carbon::parse($date);
    	$carbonNow = Carbon::now();

    	return $carbonDate->diffInSeconds($carbonNow, false) > 0;
    }

    /**
     * Check if selected date is after now or not
     * @param  [Datetime]  $date [Selected date]
     * @return boolean       [true if after]
     */
    public static function isAfterNow($date)
    {
    	$carbonDate = Carbon::parse($date);
    	$carbonNow = Carbon::now();

    	return $carbonDate->diffInSeconds($carbonNow, false) < 0;
    }
}