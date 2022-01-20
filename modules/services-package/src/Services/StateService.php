<?php


namespace Satis2020\ServicePackage\Services;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;

class StateService
{


    /**
     * @param $country_id
     * @return array|mixed
     */
    public function getStatesByCountry($country_id)
    {
        $response = Http::get(config("countries_services.countries_services_url")."countries/".$country_id."/states");
        return $response->successful()?$response->json()['states']:[];
    }

    /**
     * @param $state_id
     * @return array|mixed|null
     */
    public function getStateById($state_id)
    {
        $state = null;
        if (!is_null($state_id)){
            $response = Http::get(config("countries_services.countries_services_url")."states/".$state_id);
            $state = $response->json();
        }

        return $state;
    }
}