<?php

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Services\CountryService;
use Satis2020\ServicePackage\Services\StateService;

class AddStateToUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stateServices = new StateService();
        $units = Unit::query()
            ->whereHas("unitType",function ($query){
                $query->where('can_be_target',1);
            })->get();

        $states = $stateServices->getStatesByCountry(Constants::BENIN_COUNTRY_ID);

        foreach ($units as $unit){
            $unit->state_id =  $states[array_rand($states,1)]["id"];
            $unit->save();
        }
    }
}
