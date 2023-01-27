<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ImportClaim;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class AccountValidationForImportClaimRules
 * @package Satis2020\ServicePackage\Rules
 */
class AccountValidationForImportClaimRules implements Rule
{
    use VerifyUnicity, ImportClaim;
    protected $params;

    /**
     * AccountValidationForImportClaimRules constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public function passes($attribute, $value)
    {

        try {

            if(!$institution = Institution::where('acronyme', $this->params['acronyme'])->first()){

                return false;
            }

            $identite = $this->identiteVerifiedImport($this->params);

            if($identite){

                if(Account::with('client_institution.client' , function ($query)  use ($identite, $institution){

                    $query->whereHas('client', function($q) use ($identite, $institution){
                        $q->where('identite_id', $identite->id);
                    })->where('institution_id', $institution->id );

                })->where('number', $value)->first()){

                    return true;
                }
            }

            return false;

        } catch (\Exception $exception) {

            return false;
        }

    }

    /**
     * Get the validation error message.
     *
     * @return string|array
*/
    public function message()
    {
        return 'The account must belong to the chosen client';
    }

}
