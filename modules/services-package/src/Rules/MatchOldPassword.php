<?php

namespace Satis2020\ServicePackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

/**
 * Class MatchOldPassword
 * @package Satis2020\ServicePackage\Rules
 */
class MatchOldPassword implements Rule
{
    protected $password;

    public function __construct($password)
    {
        $this->password = $password;
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
        return Hash::check($value, $this->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is match with old password.';
    }
}
