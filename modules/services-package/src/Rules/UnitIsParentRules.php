<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;

class UnitIsParentRules implements Rule
{

    protected $claim_id;

    public function __construct($claim_id)
    {
        $this->claim_id = $claim_id;
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
        $claim = Claim::with('activeTreatment.responsibleUnit.parent')->findOrFail($this->claim_id);

        if ($claim->status==Claim::CLAIM_UNSATISFIED){
            return $claim->activeTreatment->responsibleUnit->parent->id== $value;
        }

        return  true;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The unit must be the parent of target unit';
    }

}
