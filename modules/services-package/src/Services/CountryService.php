<?php


namespace Satis2020\ServicePackage\Services;


use Illuminate\Support\Facades\Http;
use Satis\CountriesPackage\Models\Country;
use Satis2020\ServicePackage\Consts\Constants;

class CountryService
{

    public function getCountries()
    {
        $response = Http::get(config("countries_services.countries_services_url") . "africa-countries");
        return $response->successful() ? $response->json() : [];
    }
    public function getCountriesWithStates()
    {
        return Country::query()
            ->where('region', 'Africa')
            ->with('states')
            ->get();
    }
}
