<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;


/**
 * Trait ClaimCategory
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimCategory
{

    /**
     * @param bool $claimCategory
     * @return array
     */
    protected function rules($claimCategory = false){

        if($claimCategory){

            $data =  [
                'name' => ['required', new TranslatableFieldUnicityRules('claim_categories', 'name', 'id', "{$claimCategory->id}")],
                'description' => 'nullable',
                'others' => 'array',
            ];

        }else{


            $data =  [

                'name' => ['required', new TranslatableFieldUnicityRules('claim_categories', 'name')],
                'description' => 'nullable',
                'others' => 'array',
            ];
        }

        return $data;
    }


    /**
     * @param $row
     * @return mixed
     */
    protected function storeImportClaimCategory($row){

        return \Satis2020\ServicePackage\Models\ClaimCategory::create([

            'name' => $row['name'],
            'description' => $row['description']
        ]);
    }

}
