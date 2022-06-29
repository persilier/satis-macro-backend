<?php

use Satis2020\ServicePackage\Models\Claim;

if (!function_exists('getAppLang')){
    function getAppLang(){
        return app()->getLocale();
    }
}

if (!function_exists('isEscalationClaim')){
    function isEscalationClaim($claim){
        return $claim->status == Claim::CLAIM_UNSATISFIED;
    }
}