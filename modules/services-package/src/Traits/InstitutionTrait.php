<?php


namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Rules\FieldUnicityRules;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

/**
 * Trait InstitutionTrait
 * @package Satis2020\ServicePackage\Traits
 */
trait InstitutionTrait
{
    /**
     * @param $type_id
     * @param $nature
     * @return mixed
     */
    protected  function getMaximumInstitution($type_id, $nature){

        $message = __('messages.unable_to_find_unit_type',[],getAppLang());

        try {

            $maxInstitution = InstitutionType::where('id', $type_id)->where('application_type',$nature)->firstOrFail()->maximum_number_of_institutions;
            return $maxInstitution;

        } catch (\Exception $exception) {

            throw new CustomException($message);
        }

    }

    /**
     * @param $type_id
     * @param $nature
     * @return bool
     * @throws CustomException
     */
    protected function getVerifiedStore($type_id, $nature){

        $message = "Impossible de créer une institution du type sélectionné.";
        try {

            $max = $this->getMaximumInstitution($type_id, $nature);

        } catch (CustomException $e) {

            throw new CustomException($message);
        }

        if($max == 0){

            return true;
        }
        $number = Institution::where('institution_type_id', $type_id)->get()->count('id');

        if($max > $number)

            return true;
        else
            return false;
    }

    /**
     * @param bool $institution
     * @return array
     */
    protected function rules($institution = false){

        if($institution){

            $rules = [
                'name' => ['required', new FieldUnicityRules('institutions', 'name', 'id', "{$institution->id}")],
                'acronyme' => ['required', new FieldUnicityRules('institutions', 'acronyme', 'id', "{$institution->id}")],
                'iso_code' => 'required|max:50',
                'default_currency_slug' => ['nullable', 'exists:currencies,slug'],
                'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
                'institution_type_id' => 'required|exists:institution_types,id',
                'orther_attributes' => 'array',
            ];

        }else{

            $rules = [
                'name' => ['required', new FieldUnicityRules('institutions', 'name')],
                'acronyme' => ['required', new FieldUnicityRules('institutions', 'acronyme')],
                'iso_code' => 'required|max:50',
                'default_currency_slug' => ['nullable', 'exists:currencies,slug'],
                'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
                'institution_type_id' => 'required|exists:institution_types,id',
                'orther_attributes' => 'array',
            ];
        }

        return $rules;
    }


    protected function storeImportInstitution($row){

        $typeInstitution = $this->institution()->institutionType->name;

        if($typeInstitution == 'holding' || $typeInstitution == 'observatory'){

            $type = $typeInstitution == 'holding'
                ? InstitutionType::where('name', 'filiale')->firstOrFail()->id
                : InstitutionType::where('name', 'membre')->firstOrFail()->id;

        }

        return Institution::create([

            'name' => $row['name'],
            'acronyme' => $row['acronyme'],
            'iso_code' => $row['iso_code'],
            'default_currency_slug' => $row['default_currency_slug'],
            'institution_type_id' => $type,

        ]);
    }


}
