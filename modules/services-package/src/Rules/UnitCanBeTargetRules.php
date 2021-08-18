<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Unit;

class UnitCanBeTargetRules implements Rule
{

    public function __construct()
    {

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
        $unit = Unit::with('institution', 'unitType')->where('id', $value)->firstOrFail();
        try {
            $condition = $unit->unitType->can_be_target;
        } catch (\Exception $exception) {
            throw new CustomException("Can't retrieve the can_be_target attribute of the unit");
        }
        return $condition;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The unit must be targetable';
    }

}
