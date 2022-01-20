<?php


namespace Satis2020\ServicePackage\Services;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;

class CountryService
{

    public function getCountries()
    {
        $response = Http::get(env("COUNTRIES_SERVICE_URL")."africa-countries");
        return $response->successful()?$response->json():[];
    }
}