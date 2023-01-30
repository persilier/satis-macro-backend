<?php


namespace Satis2020\ServicePackage\Services;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;

class InstitutionService
{

    /**
     * @param $country_id
     * @return array|mixed|null
     */
    public function getCountryById($country_id)
    {
        $country = null;
        if (!is_null($country_id)){
            $response = Http::get(config("countries_services.countries_services_url")."countries/$country_id/states/");
            $country = $response->json();
        }

        return $country;
    }
}