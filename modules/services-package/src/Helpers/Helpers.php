<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Claim;

if (!function_exists('getAppLang')){
    function getAppLang(){
        return app()->getLocale();
    }
}

if (!function_exists('formatClaimRef')) {
    function formatClaimRef($reference)
    {
        return "[$reference]";
    }
}

if (!function_exists('extractClaimRefs')) {
    function extractClaimRefs($haystack)
    {
        $tags = array_unique(getTagContents($haystack));

        return Arr::where($tags, function ($value, $key) {
            return Str::contains(strtolower($value), 'satis');
        });
    }
}

if (!function_exists('extractPhoneNumber')) {
    function extractPhoneNumber($haystack)
    {
        return array_unique(getTagContents($haystack,"{","}"));
    }
}

function getTagContents($string, $tag_open = '[', $tag_close = ']')
{
    $result = [];
    foreach (explode($tag_open, $string) as $key => $value) {
        if (strpos($value, $tag_close) !== FALSE) {
            $result[] = substr($value, 0, strpos($value, $tag_close));
        }
    }
    return $result;
}

function conversionToDayHourMinute($minutes)
{
    
    $days = floor($minutes / 1440); // 1440 minutes in a day
    $hours = floor(($minutes % 1440) / 60); // Remaining minutes divided by 60 to get hours
    $remainingMinutes = $minutes % 60; // Remaining minutes after calculating days and hours
    
    return "$days J, $hours h, $remainingMinutes min";
}

function claimsExists($reference)
{
    return Claim::query()->where('reference',$reference)->first()!=null;
}
if (!function_exists('isEscalationClaim')){
    function isEscalationClaim($claim){
        return $claim->status == Claim::CLAIM_UNSATISFIED;
    }
}
