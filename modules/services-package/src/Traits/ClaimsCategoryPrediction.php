<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\ClaimCategory;

/**
 * Trait ClaimsCategoryPrediction
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimsCategoryPrediction
{

    protected function allClaimsCategoryPrediction($description)
    {

        /*$catData = $this->getClaimsCategory($request->description);
       $category = $catData['predictions']['categories'][0];
       $object = $catData['predictions']['objects'][$category];
       $objectId = ClaimObject::where("name->".\App::getLocale(),$object[0][0])->first()->id;
       dd($objectId->id);*/

        $categoryData = Http::post('http://10.1.1.4:5000/predict', ['description'=>$description])->json();
        $category = $categoryData['predictions']['categories'];
        $dataCategory = [];

        foreach ($category as $categories){
            $allCategories = ClaimCategory::where("name->".\App::getLocale(),$categories)->get()->toArray();
            $dataCategory = array_merge($dataCategory,$allCategories);
        }

        return $dataCategory;
    }

}
