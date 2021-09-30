<?php

namespace Satis2020\ServicePackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ExplodeTelephoneRules implements Rule
{
    protected $message;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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

        $collection = collect([]);

        if(!is_array($value)){
            $this->message = " :attribute is not an array ";
            return false;
        }

        foreach ($value as $phone) {

            $phone = preg_replace("/\s+/", "", $phone);

            if($collection->search($phone, true) !== false){
                $this->message = $phone." is sent more than once";
                return false;
            }

            $validator = Validator::make(['phone' => $phone], [
                'phone' => 'digits_between:6,14'
            ]);

            if ($validator->fails()) {
                $this->message = $phone." is not a valid phone number";
                return false;
            }

            $collection->push($phone);
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
