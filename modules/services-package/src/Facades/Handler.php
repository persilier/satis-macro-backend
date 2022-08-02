<?php

namespace Satis2020\ServicePackage\Facades;
use Illuminate\Support\Facades\Facade;

class Handler extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Handler';
    }
}
