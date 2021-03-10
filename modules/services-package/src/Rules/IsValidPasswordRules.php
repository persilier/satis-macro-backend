<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;

/**
 * Class IsValidPasswordRules
 * @package Satis2020\ServicePackage\Rules
 */
class IsValidPasswordRules implements Rule
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
        if(preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $value )){

            return true;

        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'Le mot de passe doit comporter au moins 8 caractères et doit inclure au moins une lettre majuscule et minuscule, un chiffre et un caractère spécial.';
    }

}
