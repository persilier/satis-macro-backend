<?php

namespace Satis2020\ServicePackage\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Class InstitutionValidationForImportRules
 * @package Satis2020\ServicePackage\Rules
 */
class InstitutionValidationForImportRules implements Rule
{

    protected $myInstitution;
    protected $institutionId;

    /**
     * InstitutionValidationForImportRules constructor.
     * @param $myInstitution
     */
    public function __construct($myInstitution, $institutionId)
    {
        $this->myInstitution = $myInstitution;
        $this->institutionId = $institutionId;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws CustomException
     */

    public function passes($attribute, $value)
    {

        try{

            if($this->myInstitution){

                $institution = Institution::where('acronyme', $value)->first();

                if($institution->id === $this->institutionId){
                    return true;
                }
                return false;
            }

            return true;

        }catch (\Exception $exception){

            return  false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return "La valeur du champ :attribute saisie est invalide.";
    }

}
