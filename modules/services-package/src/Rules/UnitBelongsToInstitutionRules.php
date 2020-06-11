<?php

namespace Satis2020\ServicePackage\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Unit;

class UnitBelongsToInstitutionRules implements Rule
{

    protected $institution_id;

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
        $unit = Unit::with('institution')->where('id', $value)->firstOrFail();
        try{
            $condition = $unit->institution->id === $this->institution_id;
        }catch (\Exception $exception){
            throw new CustomException("Can't retrieve the unit institution");
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
        return 'The unit must belong to the chosen institution';
    }

}
