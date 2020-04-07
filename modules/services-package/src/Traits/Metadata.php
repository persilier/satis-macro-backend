<?php
namespace Satis2020\ServicePackage\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Metadata as MetadataModel;
use Satis2020\ServicePackage\Rules\HeaderValidationRules;
use Satis2020\ServicePackage\Rules\LayoutValidationRules;

trait Metadata
{
    protected $layout = ['layout-1', 'layout-2', 'layout-3', 'layout-4'];
    // validation de la description
    protected  function rulesUpdateDescription(){
        return [
            'description' => 'required|string|max:255',
        ];
    }

    // validation de l'ajout de metadata
    protected  function rulesStoreDescription($type){
        if($type == 'models')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'fonction' => 'required|string',
            ];
        if($type == 'forms')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'content_default.layout' => [
                    'required', Rule::in(['layout-1', 'layout-2', 'layout-3', 'layout-4']),
                ],
                'content_default.action' => 'required|array',
                'content_default.action.name' => 'required|string|max:50',
                'content_default.action.endpoint' => 'required',
                'content_default' => [
                    'required','array', new LayoutValidationRules,
                ],
            ];
        if($type == 'action-forms')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'endpoint' => 'required|string',
            ];

        if($type == 'formulaire')
            return [
                'name' => 'required|string|max:50',
                'content_default.layout' => [
                    'required', Rule::in(['layout-1', 'layout-2', 'layout-3', 'layout-4']),
                ],
                'content_default.action' => 'required|array',
                'content_default.action.name' => 'required|string|max:50',
                'content_default.action.endpoint' => 'required',
                'content_default' => [
                    'required','array', new LayoutValidationRules,
                ],
            ];

        if($type == 'headers')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'content_default' => ['required', 'array', new HeaderValidationRules],
            ];
        return false;
    }

    protected function fillable_meta($type, $request){
        if($type == 'models')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'fonction' => $request->fonction,
            ];
        if($type == 'forms')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'content_default' => $request->content_default,
            ];
        if($type == 'action-forms')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'endpoint' => $request->endpoint,
            ];
        if($type == 'formulaire')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'content_default' => $request->content_default,
            ];

        if($type == 'headers')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'content_default' => $request->content_default,
            ];
        return false;
    }

    protected function getOneData($datas, $name){
        if(is_null($datas))
            return false;

        foreach ($datas as $key => $value){
            if($value->name == $name)
                return ['key' => $key,'value'=> $value];
        }
        return false;
    }


    protected  function validateOthersMeta($request, $type){
        if($type == 'models'){
            if(!is_string($request->fonction)){
                return 'La valeur de l\'attribut n\'est une chaine de caractère.';
            }
            $tab = explode('::',$request->fonction,2);
            if(count($tab)!=2){
                return 'Le format de la valeur de l\'attribut fonction est invalide.';
            }
            // model & method validation
            $model = $tab[0];
            $method = $tab[1];
            // model validation
            if (!class_exists($tab[0])) {
                return "cannot determine values for {$model}. The model provided is not a valid class.";
            }
            // method validation
            try {
                $reflection_model = new ReflectionClass($model);
                $method = $reflection_model->getMethod($method);
                if (!$method->isStatic()) {
                    return "cannot determine values for {$request->fonction}. The method provided is not a valid static method of the model provided class.";
                }
            } catch (ReflectionException $exception) {
                return "cannot determine values for {$request->fonction}. The method provided is not a valid static method of the model provided class";
            }
            return false;
        }

        if($type=='forms'){
            $actions = MetadataModel::where('name','action-forms')->get()->first() ?? abort(404);
            $actions_forms = json_decode($actions->data);
            $name = $request->content_default['action']['name'];
            $this->getOneData($actions_forms,$name);
            $action = $this->getOneData($actions_forms,$name);
            if(false!=$action){
                $endpoint = $action['value']->endpoint;
                if($endpoint!= $request->content_default['action']['endpoint'])
                    return 'Veuillez renseigner un endpoint qui existe déjà et qui corresponds avec votre action formualire choisie.';
            }else
                return 'Le nom de l\'action formualaire est invalide';
            return false;
        }
        return false;
    }

    protected function getDeleteData($datas,$key){
        $data_update = [];
        foreach ($datas as $key_1 => $value){
            if($key != $key_1)
                $data_update[] = $value;
        }
        return $data_update;
    }


    protected function getDeleteDataFroms($datas,$key){
        $data_update = [];
        foreach ($datas as $key_1 => $value){
            if($key != $key_1)
                $data_update[] = $value;
        }
        if(empty($data_update))
            return false;
        return $data_update;
    }

    protected function getCreateDataForm($datas_forms,$key,$request){
        $data_update = [];
        foreach ($datas_forms as $key_1 => $value){
            if($key == $key_1)
                $data_update[] = array(
                    "name" => $value->name,
                    "description" => $value->description,
                    "content_default" => $value->content_default,
                    "content" => $request->content_default
                );

            else
                $data_update[] = $value;
        }
        if(empty($data_update))
            return false;
        return $data_update;
    }


    /* Get All metadata   */

    protected  function getAllData($datas){
        foreach ($datas as $key => $value){
            if(!empty($value->content))
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'content' => $value->content
                );
        }
        if(empty($response_data))
            return false;
        return $response_data;
    }

    protected  function getAllDataMeta($datas, $type){

        if($type =='models'){
            foreach ($datas as $key => $value){
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'fonction' => $value->fonction
                );
            }
        }

        if($type == 'forms'){
            foreach ($datas as $key => $value){
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'content_default' => $value->content_default
                );
            }
        }

        if($type == 'action-forms'){
            foreach ($datas as $key => $value){
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'endpoint' => $value->endpoint
                );
            }
        }

        if($type == 'headers'){
            foreach ($datas as $key => $value){
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'content_default' => $value->content_default
                );
            }
        }
        if(empty($response_data))
            return false;
        return $response_data;
    }

    /*public function getData(Request $request, $datas){
        $actions = [];

        foreach ($request->get('form_actions', []) as $request_action_key => $form_action) {
            foreach ($form_action as $key => $fields) {
                $action = [];
                foreach ($fields as $field_key => $value) {
                    $action[$field_key] = $value;
                }
                $actions[$request_action_key][] = $action;
            }
        }

        return $actions;
    }*/
}
