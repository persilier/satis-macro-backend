<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Http;

/**
 * Trait ClaimsCategoryPrediction
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimsCategoryObjectPrediction
{
    protected function allClaimsCategoryObjectPrediction($description)
    {
        if ( $categoryData = Http::post(env("CLAIM_OBJECT_PREDICTION"), ['description' => $description])->json()) {
            if ( $category = $categoryData['predictions']['categories'][0]) {
                if ($objects = $categoryData['predictions']['objects'][$category][0]) {
                    $object = $objects[0];
                }
            }
        }
        /* $dataCategory = [];
         foreach ($category as $categories){
             $allCategories = ClaimCategory::where("name->".\App::getLocale(),$categories)->get()->toArray();
             $dataCategory = array_merge($dataCategory,$allCategories);
         }*/

        return $object;
    }

}
