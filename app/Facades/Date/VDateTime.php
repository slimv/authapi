<?php

namespace App\Facades\Date;
use Illuminate\Support\Facades\Facade;

class VDateTime extends Facade{
    protected static function getFacadeAccessor() { return 'vdatetime'; }
}