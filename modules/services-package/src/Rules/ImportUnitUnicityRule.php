<?php

namespace Satis2020\ServicePackage\Rules;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Institution;

/**
 * Class ImportUnitUnicityRule
 * @package Satis2020\ServicePackage\Rules
 */
class ImportUnitUnicityRule implements Rule
{
    public $params;

    /**
     * Create a new rule instance.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        try {

            $lang = app()->getLocale();

            $belongTable = DB::table($this->params['belongTable'])->whereNull('deleted_at')->get();

            if (!$belongTable = $belongTable->filter(function ($item) use ($lang) {
                $name = json_decode($item->{$this->params['belongColumn']})->{$lang};
                if($name === $this->params['belongNameValue'])
                    return $item;
            })->first()) {
                return true;
            }

            $unit = DB::table($this->params['table'])->where($this->params['foreignKey'], $belongTable->id)->whereNull('deleted_at')->get();

            if ($this->params['institution']) {
                $institution = Institution::where('name', $this->params['institution'])->first();
                $unit = $unit->where('institution_id', $institution->id)->values();
            }

            if ($search = $unit->filter(function ($item) use ($value, $lang) {
                $name = json_decode($item->{$this->params['column']})->{$lang};
                if($name === $value)
                    return $item;
            })->first()) {
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
        return 'The :attribute cannot be empty if is set';
    }

}
