<?php

namespace Satis2020\ServicePackage\Rules;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Satis2020\ServicePackage\Traits\InputsValidationRules;


class ContentFormValidation implements Rule
{
    use InputsValidationRules;
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
        if(!in_array($value['layout'], $this->layout_list)){
            $this->message = "Le type de layout '{$value['layout']}' est invalide.";
            return false;
        }

        if($value['layout']=='layout-3'){
            
            if(!is_array($value['content'])){
                $this->message = "Le format de l'attribut contenu dans le layout est invalide.";
                return false;
            }

            if(empty($value['content'])){
                $this->message = "Le format de l'attribut du contenu dans le loyout est requis.";
                return false;
            }

            // validation
            $names = [];
            foreach ($value['content'] as $param) {
                // Required validation
                foreach ($this->required_list as $required) {
                    if (!(Arr::exists($param, $required) && !is_null($param[$required]))) {
                        $this->message = "{$required} is required but not found for an element of :attribute";
                        return false;
                    }
                }

                // type validation
                if (!$this->typeValidation($param)) {
                    $this->message = "invalid type value detected for : {$param['name']}";
                    return false;
                }

                // name validation
                if (in_array($param['name'], $names)) {
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

                // multiple values validation
                if (in_array($param['type'], ['select'])) {
                    $validation = $this->multipleValuesValidation($param);

                    if (!$validation['validation']) {
                        $this->message = $validation['message'];
                        return false;
                    }
                }
            }
        }

        if($value['layout']=='layout-1'){
            for($i =0 ; $i < $this->count_panel; $i++ ){
                $num = $i+1;
                if(!empty($value['panel-'.$num])){

                    if(!is_array($value['panel-'.$num])){
                        $this->message = "Le format de l'attribut panel ".$num." dans le layout est invalide.";
                        return false;
                    }

                    if(!is_array($value['panel-'.$num]['content'])){
                        $this->message = "Le format de l'attribut du contenu dans le panel ".$num." est invalide.";
                        return false;
                    }

                    if(empty($value['panel-'.$num]['title'])){
                        $this->message = "Le titre du panel ".$num." est requis.";
                        return false;
                    }

                    if(empty($value['panel-'.$num]['content'])){
                        $this->message = "Le contenu du panel ".$num." est requis.";
                        return false;
                    }
                }
            }

        }


        if(!is_array($value['action'])){
            $this->message = "Le format de l'attribut action est invalide.";
            return false;
        }

        if(empty($value['action']['title'])){
            $this->message = "Le titre de l'action est requis.";
            return false;
        }

        if(empty($value['action']['endpoint'])){
            $this->message = "L'url de l'action est requis.";
            return false;
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
