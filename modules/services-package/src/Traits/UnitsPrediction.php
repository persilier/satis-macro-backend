<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Trait UnitsPrediction
 * @package Satis2020\ServicePackage\Traits
 */
trait UnitsPrediction
{

    protected function allUnitsPrediction($description,$object)
    {
        $unitData = Http::post('http://163.172.106.97:5006/predict', ['description' => $description,'object' => $object])->json();
        $unit = $unitData['predictions']['functions'];
        $dataUnit = [];

        foreach ($unit as $units){
            $allUnits = Unit::where("name->".\App::getLocale(),$units)->get()->toArray();
            $dataUnit = array_merge($dataUnit,$allUnits);
        }
        return $dataUnit;
    }

}
