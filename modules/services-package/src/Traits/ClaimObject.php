<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\SeverityLevel;
use Satis2020\ServicePackage\Rules\ClaimObjectUnicityRules;
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
    protected function rules($request = null, $claimObject = false)
    {


        if ($claimObject) {
            $data = [

                'name' => ['required', new ClaimObjectUnicityRules('claim_objects', 'name', $request,  'id', "{$claimObject->id}")],
                'description' => 'nullable',
                'claim_category_id' => 'required|exists:claim_categories,id',
                'severity_levels_id' => 'required|exists:severity_levels,id',
                'time_limit' => 'required|integer|min:0',
                'others' => 'array',
            ];
        } else {

            $data = [

                'name' => ['required', new ClaimObjectUnicityRules('claim_objects', 'name', $request)],
                'description' => 'nullable',
                'claim_category_id' => 'required|exists:claim_categories,id',
                'severity_levels_id' => 'required|exists:severity_levels,id',
                'time_limit' => 'required|integer|min:0',
                'others' => 'array',
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function rulesImport()
    {

        $rules = [
            'category' => ['required', 'string'],
            'object' => ['required'],
            'description' => 'nullable',
            'severity_level' => 'required|exists:severity_levels,status',
            'treatment_units' => 'nullable',
            'time_limit' => 'required|integer|min:0',
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
    protected function storeImportClaimObject($row, $nameCategory)
    {

        $lang = app()->getLocale();

        $units = [];

        $category = $row['category'];

        $institutionId = null;

        if ($this->withoutInstitution) {
            $row['institution'] = null;
        }

        $institution = Institution::query()
            ->where('name', $row['institution'])
            ->first();

        if ($institution) {
            $institutionId = $institution->id;
        }

        if (is_null($category)) {
            $category = \Satis2020\ServicePackage\Models\ClaimCategory::query()
                ->create(['name' => $nameCategory])
                ->id;
        }

        $severityLevelId = SeverityLevel::query()->where('status', $row['severity_level'])->first()->id;

        $object = \Satis2020\ServicePackage\Models\ClaimObject::query()
            ->where('name->' . $lang, $row['object'])
            ->where('claim_category_id', $category)
            ->first();

        if ($object) {
            $object->update([
                'claim_category_id' => $category,
                'description' => $row['description'],
                'severity_levels_id' => $severityLevelId,
                'time_limit' => $row['time_limit']
            ]);
        } else {
            $object = \Satis2020\ServicePackage\Models\ClaimObject::create([
                'name' => $row['object'],
                'description' => $row['description'],
                'claim_category_id' => $category,
                'severity_levels_id' => $severityLevelId,
                'time_limit' => $row['time_limit']
            ]);
        }

        if (isset($row['treatment_units']) && $treatmentUnits = explode("/", $row['treatment_units'])) {

            foreach ($treatmentUnits as $unitName) {

                $unitType = UnitType::query()
                    ->with('units')
                    ->where('name->' . $lang, $unitName)
                    ->first();

                if ($unitType) {

                    if ($unitType->can_treat) {
                        foreach ($unitType->units as $unit) {
                            $units[$unit->id] = ['institution_id' => $institutionId];
                        }
                    }
                } else {

                    $unit = Unit::query()
                        ->with('unitType')
                        ->where('name->' . $lang, $unitName)
                        ->where('institution_id', $institutionId)
                        ->first();

                    if (!$unit) {

                        $unitType = UnitType::query()->where('name->' . $lang, 'Autres')->first();

                        if (!$unitType) {
                            $unitType = UnitType::query()->create([
                                'name' => 'Autres',
                                'can_be_target' => 1,
                                'is_editable' => 1,
                                'can_treat' => 1
                            ]);
                        }

                        $unit = Unit::query()->create([
                            'name' => $unitName,
                            'unit_type_id' => $unitType->id,
                            'institution_id' => $institutionId,
                        ]);
                    }

                    if ($unit->unitType->can_treat) {
                        $units[$unit->id] = ['institution_id' => $institutionId];
                    }
                }
            }
        }

        if ($units) {
            DB::table('claim_object_unit')
                ->where('claim_object_id', $object->id)
                ->where('institution_id', '=', $institutionId)
                ->delete();

            $object->units()->sync($units);
        }

        return $object;
    }


}
