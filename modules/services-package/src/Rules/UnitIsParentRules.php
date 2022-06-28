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
        $claim = Claim::with('unitTargeted')->findOrFail($this->claim_id);

        dd($claim->unitTargeted->id, $value);
        return $claim->unitTargeted->id== $value;
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
