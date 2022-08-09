<?php
namespace Satis2020\Webhooks\Facades;

use Illuminate\Support\Facades\Facade;

class SendEvent extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'SendEvent';
    }
}