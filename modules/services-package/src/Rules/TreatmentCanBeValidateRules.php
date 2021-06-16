<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\AwaitingValidation;

class TreatmentCanBeValidateRules implements Rule
{
    use AwaitingValidation;

    public $institution_id;

    public function __construct($institution_id)
    {
        $this->institution_id = $institution_id;
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

        $claims = $this->getClaimsAwaitingValidationInMyInstitution($this->institution_id);

        return $claims->search(function ($item, $key) use ($value) {
            return $item->id == $value;
        }) !== false;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The claim treatment is already validated or it can not be validated by this pilot';
    }

}
