<?php

namespace Satis2020\ServicePackage\Traits;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Rules\ImportUnitUnicityRule;
use Satis2020\ServicePackage\Rules\NameModelRuleBelongsToParent;
use Satis2020\ServicePackage\Rules\NameModelRules;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

/***
 * Trait ImportUniteTypeUnite
 * @package Satis2020\ServicePackage\Traits
 */
trait ImportUniteTypeUnite
{

    /**
     * @param $row
     * @return array
     */
    protected function rulesImport($row){

        $rules = [
            'name_type_unite' => ['required', 'string'],
            'description_type_unite' => ['nullable', 'string'],
            'can_be_target' => ['required', Rule::in(['OUI', 'NON'])],
            'can_treat' => ['required', Rule::in(['OUI', 'NON'])],
            'name_unite' => ['required', new ImportUnitUnicityRule(['belongTable' => 'unit_types',
                'belongColumn' => 'name', 'foreignKey' => 'unit_type_id',
                'institution' => $row['institution'], 'table' => 'units',
                'column' => 'name', 'belongNameValue' => $row['name_type_unite']]
            )],
            'description_unite' => ['nullable', 'string'],
        ];

        if ($this->withoutInstitution) {

            $rules['name_unite'] = ['required', new ImportUnitUnicityRule(['belongTable' => 'unit_types',
                    'belongColumn' => 'name', 'foreignKey' => 'unit_type_id',
                    'table' => 'units', 'column' => 'name', 'belongNameValue' => $row['name_type_unite']]
            )];

        } else {
            $rules['institution'] = 'required|exists:institutions,name';
            $rules['name_unite'] = ['required', new ImportUnitUnicityRule(['belongTable' => 'unit_types',
                    'belongColumn' => 'name', 'foreignKey' => 'unit_type_id',
                    'institution' => $row['institution'], 'table' => 'units',
                    'column' => 'name', 'belongNameValue' => $row['name_type_unite']]
            )];
        }

        return $rules;
    }


    protected function storeImportUniteTypeUnite($row, $nameType){

        $type = $row['name_type_unite'];

        if(is_null($type)){

            $type = UnitType::create([
                'name' => $nameType,
                'description' => $row['description_type_unite'],
                'can_be_target' => ($row['can_be_target'] === 'OUI') ? 1 : 0,
                'can_treat' => ($row['can_treat'] === 'OUI') ? 1 : 0,
                'others' => null,
                'is_editable' => 1
            ])->id;
        }

        return Unit::create([
            'name' => $row['name_unite'],
            'description' => $row['description_unite'],
            'institution_id' => $row['institution'],
            'unit_type_id' => $type,
            'others' => null
        ]);
    }

}