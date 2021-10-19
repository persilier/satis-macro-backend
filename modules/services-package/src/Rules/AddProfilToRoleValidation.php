<?php

namespace Satis2020\ServicePackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Models\Institution;
use Spatie\Permission\Models\Role;

class AddProfilToRoleValidation implements Rule
{
    protected $message;

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
        $value = explode('/', $value);

        if(!is_array($value)){
            $this->message = " :attribute is not an array ";
            return false;
        }

        foreach ($value as $item) {
            if (!$role = Role::where('name', $item)->where('guard_name', 'api')->first()) {
                $this->message = " :attribute : ".$item." n'existe pas.";
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
