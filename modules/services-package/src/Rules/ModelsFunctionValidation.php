<?php

namespace satis2020\ServicePackage\Rules;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;
use Satis2020\ServicePackage\Traits\ApiResponser;

class ContentFormValidation implements Rule
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
        if(!is_string($value)){
            $this->message = "La valeur de l'attribut n'est une chaine de caractère.";
            return false;
        }

        if($tab = explode('::',$value,2)){
            $this->message = "Le format de la valeur de l'attribut fonction est invalide.";
            return false;
        }

        // model & method validation
        if ((!empty($tab[0])) && (!empty($tab[1]))) {
            $model = $tab[0];
            $method = $tab[1];
            // model validation
            if (!class_exists($tab[0])) {
                $this->message = "cannot determine values for {$model}. The model provided is not a valid class";
                return false;
            }

            // method validation
            try {
                $reflection_model = new ReflectionClass($model);
                $method = $reflection_model->getMethod($method);
                if (!$method->isStatic()) {
                    $this->message =  "cannot determine values for {$value}. The method provided is not a valid static method of the model provided class";
                    return false;
                }
            } catch (ReflectionException $exception) {
                $this->message =  "cannot determine values for {$value}. The method provided is not a valid static method of the model provided class";
                return false;
            }

            /*if (!$this->confirmValues(call_user_func($model.'::'.$method))) {
                $this->message =  "cannot determine values for {$value}. values provided is not an expected array";
                return false;
            }*/

        }
    
        return true;
    }


    /*protected function confirmValues($values)
    {
        if (!is_array($values)) {
            return false;
        }

        $collection = collect($values);

        return $collection->every(function ($value, $key) {
            $keys = array_keys($value);
            return count($keys) == 2 && in_array('label', $keys) && in_array('value', $keys);
        });
    }*/

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
