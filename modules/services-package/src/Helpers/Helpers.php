<?php

if (!function_exists('getAppLang')){
    function getAppLang(){
        return app()->getLocale();
    }
}