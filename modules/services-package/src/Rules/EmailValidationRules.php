<?php

namespace Satis2020\ServicePackage\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;

class EmailValidationRules implements Rule
{
    private $message;

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
        if(!is_array($value)){
            $this->message = "Le champ adresse email doit être un tableau.";
            return false;
        }
        foreach ($value as $key => $param) {
            if(!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $param)){
                $this->message = "La valeur ".$param." n'est pas au format d'adresse email.";
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
