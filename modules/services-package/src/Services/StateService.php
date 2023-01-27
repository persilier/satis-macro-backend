<?php


namespace Satis2020\ServicePackage\Services;


use Illuminate\Support\Facades\Http;
use Satis\CountriesPackage\Models\Country;
use Satis\CountriesPackage\Models\State;
use Satis2020\ServicePackage\Consts\Constants;

class StateService
{


    /**
     * @param $country_id
     * @return array|mixed
     */
    public function getStatesByCountry($country_id)
    {
        $country =  Country::query()
            ->with("states")
            ->where('region', 'Africa')
            ->where('id', $country_id)
            ->first();
        return $country!=null?$country->states:[];
    }

    /**
     * @param $state_id
     * @return array|mixed|null
     */
    public function getStateById($state_id)
    {
        $state = null;
        if (!is_null($state_id)){
            $state = State::query()->find($state_id);
        }

        return $state;
    }
}