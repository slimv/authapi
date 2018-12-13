<?php

namespace App\Facades\String;
use Illuminate\Support\Facades\Facade;

class VString extends Facade{
    protected static function getFacadeAccessor() { return 'vstring'; }
}