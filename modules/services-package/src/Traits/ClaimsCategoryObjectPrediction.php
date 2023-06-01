<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Trait ClaimsCategoryPrediction
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimsCategoryObjectPrediction
{
    protected function allClaimsCategoryObjectPrediction($description)
    {
        

        try {
            $categoryData = Http::post(Config::get("email-claim-configuration.claim_object_prediction"), ['description' => $description])->json();
            if ($categoryData) {
                if ( $category = $categoryData['predictions']['categories'][0]) {
                    if ($objects = $categoryData['predictions']['objects'][$category][0]) {
                        $object = $objects[0];
                    }
                }
            }
            return $object;
        } catch (\Exception $th) {
            //throw $th;

            return null;
        }
       
        /* $dataCategory = [];
         foreach ($category as $categories){
             $allCategories = ClaimCategory::where("name->".\App::getLocale(),$categories)->get()->toArray();
             $dataCategory = array_merge($dataCategory,$allCategories);
         }*/


    }

}
