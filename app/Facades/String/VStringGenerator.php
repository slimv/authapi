<?php

namespace App\Facades\String;
use Illuminate\Support\Facades\Facade;

class VStringGenerator extends Facade{
    protected static function getFacadeAccessor() { return 'vstringgenerator'; }
}