<?php


namespace Satis2020\ServicePackage\Services;


use Satis\CountriesPackage\Models\Country;

class CountryService
{

    public function getCountries()
    {
        return Country::query()
            ->where('region', 'Africa')
            ->with('states')
            ->get();
    }
}