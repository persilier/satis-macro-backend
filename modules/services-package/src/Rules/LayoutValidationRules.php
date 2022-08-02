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

        if(!in_array(isset($value['layout']), $this->layout_list)){
            $this->message = "Le champ layout de l'object json content_default est requis, avec les valeurs possible layout-1, layout-2, layout-3 ou layout-4.";
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
                    $this->message = "Le format de l'attribut du contenu dans le panel ".$num." est invalide. Veuiller renseigner un format d'object json.";
                    return false;
                }

                $names = [];
                foreach ($value['panel-'.$num]['content'] as $param) {
                    foreach ($this->required_list as $required) {
                        if (!(Arr::exists($param, $required) && !is_null($param[$required]))) {
                            $this->message = "{$required} est requis mais introuvable pour un élément de :attribute";
                            return false;
                        }
                    }
                    // type validation
                    if (!$this->typeValidation($param)) {
                        $this->message = "valeur de type non valide détectée pour : {$param['name']}";
                        return false;
                    }
                    // name validation
                    if (in_array($param['name'], $names)) {
                        $this->message = "valeur de nom en double donnée : {$param['name']}";
                        return false;
                    }
                    $names[] = $param['name'];
                    // visible validation
                    if (!$this->visibleValidation($param)) {
                        $this->message = "valeur visible non valide détectée pour : {$param['name']}";
                        return false;
                    }

                    // required validation
                    if (!$this->requiredValidation($param)) {
                        $this->message = "valeur requise non valide détectée pour : {$param['name']}";
                        return false;
                    }
                    // multiple values validation
                    if (in_array($param['type'], ['select'])) {
                        $validation = $this->modelSelectValidation($param);

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
                            $this->message = "{$required} est requis mais introuvable pour un élément de :attribute";
                            return false;
                        }
                    }
                    // type validation
                    if (!$this->typeValidation($param)) {
                        $this->message = "valeur de type non valide détectée pour : {$param['name']}";
                        return false;
                    }
                    // name validation
                    if (in_array($param['name'], $names)) {
                        $this->message = "valeur de nom en double donnée : {$param['name']}";
                        return false;
                    }
                    $names[] = $param['name'];
                    // visible validation
                    if (!$this->visibleValidation($param)) {
                        $this->message = "valeur visible non valide détectée pour : {$param['name']}";
                        return false;
                    }

                    // required validation
                    if (!$this->requiredValidation($param)) {
                        $this->message = "valeur requise non valide détectée pour : {$param['name']}";
                        return false;
                    }
                    // multiple values validation
                    if (in_array($param['type'], ['select'])) {
                        $validation = $this->modelSelectValidation($param);

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
                        $this->message = "{$required} est requis mais introuvable pour un élément de :attribute";
                        return false;
                    }
                }
                // type validation
                if (!$this->typeValidation($param)) {
                    $this->message = "valeur de type non valide détectée pour : {$param['name']}";
                    return false;
                }
                // name validation
                if (in_array($param['name'], $names)) {
                    $this->message = "valeur de nom en double donnée : {$param['name']}";
                    return false;
                }
                $names[] = $param['name'];
                // visible validation
                if (!$this->visibleValidation($param)) {
                    $this->message = "valeur visible non valide détectée pour : {$param['name']}";
                    return false;
                }

                // required validation
                if (!$this->requiredValidation($param)) {
                    $this->message = "valeur requise non valide détectée pour : {$param['name']}";
                    return false;
                }
                // multiple values validation
                if (in_array($param['type'], ['select'])) {
                    $validation = $this->modelSelectValidation($param);

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
                            $this->message = "{$required} est requis mais introuvable pour un élément de :attribute";
                            return false;
                        }
                    }
                    // type validation
                    if (!$this->typeValidation($param)) {
                        $this->message = "valeur de type non valide détectée pour : {$param['name']}";
                        return false;
                    }
                    // name validation
                    if (in_array($param['name'], $names)) {
                        $this->message = "valeur de nom en double donnée : {$param['name']}";
                        return false;
                    }
                    $names[] = $param['name'];
                    // visible validation
                    if (!$this->visibleValidation($param)) {
                        $this->message = "valeur visible non valide détectée pour : {$param['name']}";
                        return false;
                    }

                    // required validation
                    if (!$this->requiredValidation($param)) {
                        $this->message = "valeur requise non valide détectée pour : {$param['name']}";
                        return false;
                    }
                    // multiple values validation
                    if (in_array($param['type'], ['select'])) {
                        $validation = $this->modelSelectValidation($param);
                        if (!$validation['validation']) {
                            $this->message = $validation['message'];
                            return false;
                        }
                    }
                }

            }
        }

        if(!isset($value['action'])){
            $this->message = "Le champ action est requis et doit être un objet json.";
            return false;
        }

        if(!is_array($value['action'])){
            $this->message = "Le champ action doit être un objet json.";
            return false;
        }

        if(!isset($value['action']['name'])){
            $this->message = "Le champ name de l'objet json action est requis.";
            return false;
        }

        if(!isset($value['action']['title'])){
            $this->message = "Le champ titre de l'objet json action est requis.";
            return false;
        }

        if(!isset($value['action']['endpoint'])){
            $this->message = "Le champ endpoint de l'objet json action est requis.";
            return false;
        }
        return true;
    }

    protected function validInput($value){
        $names = [];
        foreach ($value as $param) {
            foreach ($this->required_list as $required) {
                if (!(Arr::exists($param, $required) && !is_null($param[$required]))) {
                    $this->message = "{$required} est requis mais introuvable pour un élément de :attribute";
                    return false;
                }
            }
            // type validation
            if (!$this->typeValidation($param)) {
                $this->message = "valeur de type non valide détectée pour : {$param['name']}";
                return false;
            }
            // name validation
            if (in_array($param['name'], $names)) {
                $this->message = "valeur de nom en double donnée : {$param['name']}";
                return false;
            }
            $names[] = $param['name'];
            // visible validation
            if (!$this->visibleValidation($param)) {
                $this->message = "valeur visible non valide détectée pour : {$param['name']}";
                return false;
            }

            // required validation
            if (!$this->requiredValidation($param)) {
                $this->message = "valeur requise non valide détectée pour : {$param['name']}";
                return false;
            }
            // multiple values validation
            if (in_array($param['type'], ['select'])) {
                $validation = $this->modelSelectValidation($param);

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
