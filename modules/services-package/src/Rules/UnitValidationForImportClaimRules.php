<?php

namespace Satis2020\ServicePackage\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Class UnitValidationForImportClaimRules
 * @package Satis2020\ServicePackage\Rules
 */
class UnitValidationForImportClaimRules implements Rule
{

    protected $params;

    /**
     * UnitValidationForImportClaimRules constructor.
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
     * @throws CustomException
     */

    public function passes($attribute, $value)
    {
        try {

            $lang = app()->getLocale();

            $data = DB::table($this->params['table'])->whereNull('deleted_at')->get();

            if(!$unit = $data->filter(function ($item) use ($value, $lang) {

                $name = json_decode($item->{$this->params['column']})->{$lang};

                if($name === $value)
                    return $item;

            })->first()){
                return false;
            }

            $unit = Unit::with('institution', 'unitType')->find($unit->id);

            if ($unit->institution->acronyme !== $this->params['acronyme']) {
                return false;
            }

            if (!$unit->unitType->can_be_target) {
                return false;
            }
            return true;
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
        return 'The unit must belong to the chosen institution';
    }

}
