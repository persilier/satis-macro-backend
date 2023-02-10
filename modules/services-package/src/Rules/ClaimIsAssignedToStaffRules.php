<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;

class ClaimIsAssignedToStaffRules implements Rule
{

    protected $staff_id;

    public function __construct($staff_id)
    {
        $this->staff_id = $staff_id;
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
        $claim = Claim::with('activeTreatment')->findOrFail($value);

        return ($claim->activeTreatment->responsible_staff_id == $this->staff_id) || isEscalationClaim($claim);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The claim is not assigned to you';
    }
}
