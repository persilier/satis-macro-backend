<?php

namespace Satis2020\ServicePackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class ExplodeEmailRules
 * @package Satis2020\ServicePackage\Rules
 */
class ExplodeEmailRules implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

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

        $value = array_map('strtolower', $value);

        $collection = collect([]);

        if(!is_array($value)){
            $this->message = " :attribute is not an array ";
            return false;
        }

        foreach ($value as $email) {

            $email = trim($email);

            if($collection->search($email, true) !== false){
                $this->message = $email." is sent more than once";
                return false;
            }

            $validator = Validator::make(['email' => $email], [
                'email' => 'email'
            ]);

            if ($validator->fails()) {
                $this->message = $email." is not a valid address mail";
                return false;
            }

            $collection->push($email);
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
