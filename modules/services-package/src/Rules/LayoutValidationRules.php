<?php

namespace Satis2020\ServicePackage\Rules;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Satis2020\ServicePackage\Traits\InputsValidationRules;


class LayoutValidationRules implements Rule
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
        if(empty($value['layout'])){
            $this->message = "Le type de layout est requis avec les valeurs possible layout-1, layout-2, layout-3 ou layout-4.";
            return false;
        }
        if(!in_array($value['layout'], $this->layout_list)){
            $this->message = "Le type de layout '{$value['layout']}' est invalide.";
            return false;
        }

        if($value['layout']=='layout-1'){
            $n = 3;
            while (isset($value['panel-'.$n])){
                $this->message = "Le panel ".$n." ne doit pas exister dans le layout 1.";
                return false;
            }
            for($i = 1 ; $i <= 2; $i++){
                $num = $i;
                if(empty($value['panel-'.$num])){
                    $this->message = "Le contenu du panel ".$num." est requis.";
                    return false;
                }

                if(!is_array($value['panel-'.$num])){
                    $this->message = "Le format de l'attribut panel ".$num." dans le layout est invalide.";
                    return false;
                }
                //if(!empty($value['panel-'.$num])){
                if(empty($value['panel-'.$num]['title'])){
                    $this->message = "Le titre du panel ".$num." est requis.";
                    return false;
                }

                if(empty($value['panel-'.$num]['content'])){
                    $this->message = "Le contenu du panel ".$num." est requis.";
                    return false;
                }

                if(!is_array($value['panel-'.$num]['content'])){
                    $this->message = "Le format de l'attribut du contenu dans le panel ".$num." est invalide.";
                    return false;
                }

                $names = [];
                foreach ($value['panel-'.$num]['content'] as $param) {
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
        }

        if($value['layout']=='layout-2'){
            $n = 2;
            while (isset($value['panel-'.$n])){
                $this->message = "Le panel ".$n." ne doit pas exister dans le layout 2.";
                return false;
            }

            for($i =1 ; $i <= 1; $i++ ){
                $num = $i;
                if(empty($value['panel-'.$num])){
                    $this->message = "Le contenu du panel ".$num." est requis.";
                    return false;
                }

                if(!is_array($value['panel-'.$num])){
                    $this->message = "Le format de l'attribut panel ".$num." dans le layout est invalide.";
                    return false;
                }
                //if(!empty($value['panel-'.$num])){
                if(empty($value['panel-'.$num]['title'])){
                    $this->message = "Le titre du panel ".$num." est requis.";
                    return false;
                }

                if(empty($value['panel-'.$num]['content'])){
                    $this->message = "Le contenu du panel ".$num." est requis.";
                    return false;
                }

                if(!is_array($value['panel-'.$num]['content'])){
                    $this->message = "Le format de l'attribut du contenu dans le panel ".$num." est invalide.";
                    return false;
                }

                $names = [];
                foreach ($value['panel-'.$num]['content'] as $param) {
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
        }

        if($value['layout']=='layout-3'){
            if(empty($value['content'])){
                $this->message = "Le format de l'attribut du contenu dans le layout 3 est requis.";
                return false;
            }

            if(!is_array($value['content'])){
                $this->message = "Le format de l'attribut contenu dans le layout 3 est invalide.";
                return false;
            }

            $names = [];
            foreach ($value['content'] as $param) {
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

        if($value['layout']=='layout-4'){
            $n = 4;
            while (isset($value['panel-'.$n])){
                $this->message = "Le panel ".$n." ne doit pas exister dans le layout 4.";
                return false;
            }
            for($i =1 ; $i <= 3; $i++ ){
                $num = $i;
                if(empty($value['panel-'.$num])){
                    $this->message = "Le contenu du panel ".$num." est requis.";
                    return false;
                }

                if(!is_array($value['panel-'.$num])){
                    $this->message = "Le format de l'attribut panel ".$num." dans le layout est invalide.";
                    return false;
                }
                //if(!empty($value['panel-'.$num])){
                if(empty($value['panel-'.$num]['title'])){
                    $this->message = "Le titre du panel ".$num." est requis.";
                    return false;
                }

                if(empty($value['panel-'.$num]['content'])){
                    $this->message = "Le contenu du panel ".$num." est requis.";
                    return false;
                }

                if(!is_array($value['panel-'.$num]['content'])){
                    $this->message = "Le format de l'attribut du contenu dans le panel ".$num." est invalide.";
                    return false;
                }

                $names = [];
                foreach ($value['panel-'.$num]['content'] as $param) {
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
        }
        return true;
    }

    protected function validInput($value){
        $names = [];
        foreach ($value as $param) {
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
