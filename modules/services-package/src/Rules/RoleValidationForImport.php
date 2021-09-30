<?php

namespace Satis2020\ServicePackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Models\Institution;
use Spatie\Permission\Models\Role;

class RoleValidationForImport implements Rule
{
    protected $institutionName;
    protected $message;
    /**
     * Create a new rule instance.
     *
     * @param $institutionName
     */
    public function __construct($institutionName)
    {
        $this->institutionName = $institutionName;
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

            if (! $role = Role::where('name', $item)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->first()) {
                $this->message = " :attribute : ".$item." n'existe pas.";
                return false;
            }

            if (! $institution = Institution::with('institutionType')->where('name', $this->institutionName)->first()) {
                $this->message = " :attribute : ".$item." la valeur de l'institution est invalide.";
                return false;
            }

            if(! in_array($institution->institutionType->name, $role->institution_types)){
                $this->message = " :attribute : ".$item." est invalide.";
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
