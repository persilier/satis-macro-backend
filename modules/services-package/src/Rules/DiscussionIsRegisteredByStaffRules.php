<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;

class DiscussionIsRegisteredByStaffRules implements Rule
{

    protected $discussion;
    protected $staff;

    public function __construct($discussion, $staff)
    {
        $this->discussion = $discussion;
        $this->staff = $staff;
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
        return $this->staff->id == $this->discussion->createdBy->id;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'You are not authorized to perform this action';
    }

}
