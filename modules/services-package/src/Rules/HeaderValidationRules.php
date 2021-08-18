<?php

namespace Satis2020\ServicePackage\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Satis2020\ServicePackage\Traits\InputsValidationRules;


class HeaderValidationRules implements Rule
{
    use InputsValidationRules;

    public function __construct()
    {

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
        $names = [];
        foreach ($value as $key => $param) {
            if(!is_array($param)){
                $this->message = "Le format de l'élément ".$key." est invalide";
                return false;
            }

            foreach ($this->required_list_hearder as $required) {
                if (!(Arr::exists($param, $required) && !is_null($param[$required]))) {
                    $this->message = "{$required} is required but not found for an element of :attribute";
                    return false;
                }
            }
            // name validation
            if(in_array($param['name'], $names)) {
                $this->message = "duplicate name value given : {$param['name']}";
                return false;
            }

            $names[] = $param['name'];
            // visible validation
            if (!$this->visibleValidation($param)) {
                $this->message = "invalid visible value detected for : {$param['name']}";
                return false;
            }

            // required validation
            if (!$this->requiredValidation($param)) {
                $this->message = "invalid required value detected for : {$param['name']}";
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return $this->message;
    }

}
