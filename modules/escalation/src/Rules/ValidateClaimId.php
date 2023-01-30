<?php

namespace Satis2020\Escalation\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Services\TreatmentBoardService;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Services\StateService;

class ValidateClaimId implements Rule
{



    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public function passes($attribute, $value)
    {
        if ($value == TreatmentBoard::STANDARD){
            $valid = true;
        }else{
            $valid = !is_null(Claim::query()->find($value));
        }
        return $valid;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'Le commité standad existe déjà';
    }

}
