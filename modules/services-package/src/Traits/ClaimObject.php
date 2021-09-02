<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\SeverityLevel;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
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

         $rules = [
            'category' => ['required', 'string'],
            'object' => ['required'],
            'description' => 'nullable',
            'severity_level' => 'required|exists:severity_levels,status',
            'treatment_units' => 'nullable',
            'time_limit' => 'required|integer',
        ];

         if (!$this->withoutInstitution) {
             $rules['institution'] = 'required|exists:institutions,name';
         }

         return $rules;
    }


    /**
     * @param $row
     * @param $nameCategory
     * @return mixed
     */
    protected function storeImportClaimObject($row, $nameCategory){

        $lang = app()->getLocale();

        $units = collect([]);

        $category = $row['category'];

        $institutionId = null;

        if ($this->withoutInstitution) {
            $row['institution'] = null;
        }

        if ($institution = Institution::where('name', $row['institution'])->first()) {
            $institutionId = $institution->id;
        }

        if(is_null($category)){

            $category = \Satis2020\ServicePackage\Models\ClaimCategory::create(['name' => $nameCategory])->id;
        }

        if ($object = \Satis2020\ServicePackage\Models\ClaimObject::where('name->'.$lang)->first()) {

            $object->update(['claim_category_id' => $category]);

        } else {

            $object = \Satis2020\ServicePackage\Models\ClaimObject::create([
                'name' => $row['object'],
                'description' => $row['description'],
                'claim_category_id' => $category,
                'severity_levels_id' => SeverityLevel::where('status', $row['severity_level'])->first()->id,
                'time_limit' => $row['time_limit']
            ]);
        }

        if (isset($row['treatment_units']) && $treatmentUnits = explode("/",$row['treatment_units'])) {
            foreach ($treatmentUnits as $unitName) {

                if (!$unit = Unit::where('name->'.$lang, $unitName)->where('institution_id', $institutionId)->first()) {
                    if (!$unitype = UnitType::where('name->'.$lang, 'Autres')->first()) {
                        $unitype = UnitType::create([
                            'name' => 'Autres',
                            'can_be_target' => 1,
                            'is_editable' => 1,
                            'can_treat' => 1
                        ]);
                    }

                    $unit = Unit::create([
                        'name' => $unitName,
                        'unit_type_id' => $unitype->id,
                        'institution_id' => $institutionId,
                    ]);
                }

                $units->push($unit);
            }
        }

        if ($units->isNotEmpty()) {
            $object->units()->whereHas('unitType', function ($q){
                $q->where('can_treat', 1);
            })->wherePivot('institution_id', $institutionId)->sync($units->pluck('id'));
        }

        return $object;
    }

}
