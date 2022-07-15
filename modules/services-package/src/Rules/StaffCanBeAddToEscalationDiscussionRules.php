<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Traits\Discussion;

class StaffCanBeAddToEscalationDiscussionRules implements Rule
{

    use Discussion, ClaimAwaitingTreatment;

    protected $discussion;

    public function __construct($discussion)
    {
        $this->discussion = $discussion;
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
        return $this->getTargetedStaffFromUnit($this->getNormalTreatment($this->discussion->claim_id)->responsible_unit_id)->search(function ($item, $key) use ($value) {
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
        return 'This staff does not belong to the list of the possible contributors of the discussion';
    }

}
