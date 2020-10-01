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

            'name' => ['required', new TranslatableFieldUnicityRules('claim_objects', 'name')],
            'description' => 'nullable',
            'claim_category_id' => ['required', new NameModelRules(['table' => 'claim_categories', 'column'=> 'name'])],
            'severity_levels_id' => ['required', new NameModelRules(['table' => 'severity_levels', 'column'=> 'name'])],
            'time_limit' => 'required|integer',
            'others' => 'array',
        ];
    }


    /**
     * @param $row
     * @return mixed
     */
    protected function storeImportClaimObject($row){

        return \Satis2020\ServicePackage\Models\ClaimObject::create([

            'name' => $row['name'],
            'description' => $row['description'],
            'claim_category_id' => $row['claim_category'],
            'severity_levels_id' => $row['severity_level'],
            'time_limit' => $row['time_limit'],
            'others'  => $row['others']
        ]);
    }

}
