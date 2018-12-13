<?php
namespace App\Helpers\String;

use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use VDateTime;

class VStringGenerator {
    /**
     * Function to generate string from temple string
     * Note:
     * [YEAR] -> [4 space] current year. E.g: 2018
     * [MONTH] -> [2 space] current month. E.g: 01
     * [DAY] -> [2 space] current day. E.g: 01
     * [C] => [1 space] random uppercase character (A-Z only). E.g: A
     * [N] => [1 space] random number . E.g: 1
     * [W] => [1 space] random number or uppercase character . E.g: A or 1
     * [c] => [1 space] random lowercase character (a-z only). E.g: a
     * [w] => [1 space] random number or lowercase character . E.g: a or 1
     * @param  [type] $template [description]
     * @return [type]           [description]
     */
    public function generateStringFromTemplate($template) 
    {
        if(!$template) {
            return null;
        }

        $now = VDateTime::now();

        $year = str_pad($now->year, 4, '0', STR_PAD_LEFT);
        $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($now->daysInMonth, 2, '0', STR_PAD_LEFT);

        $generatedString = $template;

        $generatedString = str_replace("[YEAR]", $year, $generatedString);
        $generatedString = str_replace("[MONTH]", $month, $generatedString);
        $generatedString = str_replace("[day]", $year, $generatedString);

        while(strpos($generatedString, '[C]') !== false && strpos($generatedString, '[C]') >= 0) {
            $value = $this->getRandomCharacters(1, false, true);
            $generatedString = $this->str_replace_first("[C]", $value, $generatedString);
        }
        while(strpos($generatedString, '[N]') !== false && strpos($generatedString, '[N]') >= 0) {
            $value = $this->getRandomNumber(1);
            $generatedString = $this->str_replace_first("[N]", $value, $generatedString);
        }
        while(strpos($generatedString, '[W]') !== false && strpos($generatedString, '[W]') >= 0) {
            $value = $this->getRandomText(1, false, true);
            $generatedString = $this->str_replace_first("[W]", $value, $generatedString);
        }
        while(strpos($generatedString, '[c]') !== false && strpos($generatedString, '[c]') >= 0) {
            $value = $this->getRandomCharacters(1, false, false);
            $generatedString = $this->str_replace_first("[c]", $value, $generatedString);
        }
        while(strpos($generatedString, '[w]') !== false && strpos($generatedString, '[w]') >= 0) {
            $value = $this->getRandomText(1, false, false);
            $generatedString = $this->str_replace_first("[w]", $value, $generatedString);
        }

        return $generatedString;
    }

    function str_replace_first($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $content, 1);
    }

    function getRandomCharacters($length = 10, $ignoreCase = false , $uppercase = false) {
        if($ignoreCase) {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            if($uppercase) {
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            } else {
                $characters = 'abcdefghijklmnopqrstuvwxyz';
            }
        }

        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getRandomText($length = 10, $ignoreCase = false , $uppercase = false) {
        if($ignoreCase) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            if($uppercase) {
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            } else {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
            }
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getRandomNumber($length = 10) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate random string
     * @param  [int] $length [Random string length]
     * @return [string]         [Random string]
     */
    function randomString($length) 
    {
        return str_random($length);
    }
}