<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proxy parameter
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'http_proxy' => env('HTTP_PROXY', ''),

    'https_proxy' => env('HTTPS_PROXY', ''),

    'no_proxy' => env('NO_PROXY') ? explode(',', env('NO_PROXY')) : [],

];
