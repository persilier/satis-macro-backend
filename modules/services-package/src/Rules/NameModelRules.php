<?php

namespace Satis2020\ServicePackage\Rules;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Institution;

/**
 * Class NameModelRules
 * @package Satis2020\ServicePackage\Rules
 */
class NameModelRules implements Rule
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

            $data = DB::table($this->params['table'])->whereNull('deleted_at')->get();

            return $search = $data->filter(function ($item) use ($value, $lang) {

                $name = json_decode($item->{$this->params['column']})->{$lang};

                if($name === $value)
                    return $item;

            })->first();

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
