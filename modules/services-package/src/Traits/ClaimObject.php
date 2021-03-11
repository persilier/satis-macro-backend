<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Rules\NameModelRules;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;


/**
 * Trait ClaimObject
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimObject
{
    /**
     * @param bool $claimObject
     * @return array
     */
    protected function rules($claimObject = false){


        if($claimObject){

            $data =  [

                'name' => ['required', new TranslatableFieldUnicityRules('claim_objects', 'name', 'id', "{$claimObject->id}")],
                'description' => 'nullable',
                'claim_category_id' => 'required|exists:claim_categories,id',
                'severity_levels_id' => 'exists:severity_levels,id',
                'time_limit' => 'required|integer',
                'others' => 'array',
            ];

        }else{

            $data  =  [

                'name' => ['required', new TranslatableFieldUnicityRules('claim_objects', 'name')],
                'description' => 'nullable',
                'claim_category_id' => 'required|exists:claim_categories,id',
                'severity_levels_id' => 'exists:severity_levels,id',
                'time_limit' => 'required|integer',
                'others' => 'array',
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function rulesImport(){

        return [
            'category' => ['required', 'string'],
            'object' => ['required', new TranslatableFieldUnicityRules('claim_objects', 'name')],
            'description' => 'nullable',
            'severity_level' => ['required', new NameModelRules(['table' => 'severity_levels', 'column'=> 'name'])],
            'time_limit' => 'required|integer',
        ];
    }


    /**
     * @param $row
     * @param $nameCategory
     * @return mixed
     */
    protected function storeImportClaimObject($row, $nameCategory){

        $category = $row['category'];

        if(is_null($category)){

            $category = \Satis2020\ServicePackage\Models\ClaimCategory::create(['name' => $nameCategory])->id;
        }

        return \Satis2020\ServicePackage\Models\ClaimObject::create([

            'name' => $row['object'],
            'description' => $row['description'],
            'claim_category_id' => $category,
            'severity_levels_id' => $row['severity_level'],
            'time_limit' => $row['time_limit']
        ]);
    }

}
