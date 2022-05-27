<?php

namespace Satis2020\ServicePackage\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Metadata as MetadataModel;
use Satis2020\ServicePackage\Repositories\MetadataRepository;
use Satis2020\ServicePackage\Rules\HeaderValidationRules;
use Satis2020\ServicePackage\Rules\LayoutValidationRules;

trait Metadata
{
    protected $layout = ['layout-1', 'layout-2', 'layout-3', 'layout-4'];


    // validation de la description
    protected function rulesUpdateDescription()
    {
        return [
            'description' => 'required|string|max:255',
        ];
    }

    // validation de l'ajout de metadata
    protected function rulesStoreDescription($type)
    {
        if ($type == 'models')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'fonction' => 'required|string',
            ];
        if ($type == 'forms')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'content_default' => [
                    'required', 'array', new LayoutValidationRules,
                ],
            ];
        if ($type == 'action-forms')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'endpoint' => 'required|string',
            ];

        if ($type == 'formulaire')
            return [
                'name' => 'required|string|max:50',
                'content_default' => [
                    'required', 'array', new LayoutValidationRules,
                ],
            ];

        if ($type == 'headers')
            return [
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
            ];

        if ($type == 'headers-update')
            return [
                'name' => 'required|string|max:50',
                'content_default' => ['required', 'array', new HeaderValidationRules],
            ];

        if ($type == 'installation-steps') {
            $number_steps = collect(json_decode(MetadataModel::where('name', 'installation-steps')->first()->data))->count();
            return [
                'name' => 'required|integer|size:' . ($number_steps + 1),
                'family' => ['required', Rule::in(["nature", "register-form", "register-header"])],
                'title' => 'required|string',
                'content' => 'required',
            ];
        }

        if ($type == 'app-nature') {
            return [
                'nature' => ['required', Rule::in(collect(json_decode(MetadataModel::where('name', 'app-types')->first()->data))->pluck('libelle')->all())]
            ];
        }

        return false;
    }

    protected function fillable_meta($type, $request)
    {
        if ($type == 'models')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'fonction' => $request->fonction,
            ];
        if ($type == 'forms')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'content_default' => $request->content_default,
            ];
        if ($type == 'action-forms')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'endpoint' => $request->endpoint,
            ];
        if ($type == 'formulaire')
            return [
                'name' => $request->name,
                'description' => $request->description,
                'content_default' => $request->content_default,
            ];

        if ($type == 'headers')
            return [
                'name' => $request->name,
                'description' => $request->description,
            ];

        if ($type == 'installation-steps')
            return [
                'name' => $request->name,
                'family' => $request->family,
                'title' => $request->title,
                'content' => $request->content
            ];

        if ($type == 'app-nature') {
            return [
                'nature' => $request->nature
            ];
        }

        return false;
    }

    protected function getOneData($datas, $name)
    {
        if (is_null($datas))
            return false;

        foreach ($datas as $key => $value) {
            if ($value->name == $name)
                return ['key' => $key, 'value' => $value];
        }
        return false;
    }


    protected function validateOthersMeta($request, $type)
    {
        if ($type == 'models') {
            if (!is_string($request->fonction)) {
                return 'La valeur de l\'attribut n\'est une chaine de caractère.';
            }
            $tab = explode('::', $request->fonction, 2);
            if (count($tab) != 2) {
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

        if ($type == 'forms') {
            $actions = MetadataModel::where('name', 'action-forms')->get()->first() ?? abort(404);
            $actions_forms = json_decode($actions->data);
            $name = $request->content_default['action']['name'];
            $this->getOneData($actions_forms, $name);
            $action = $this->getOneData($actions_forms, $name);
            if (false != $action) {
                $endpoint = $action['value']->endpoint;
                if ($endpoint != $request->content_default['action']['endpoint'])
                    return 'Veuillez renseigner un endpoint qui existe déjà et qui corresponds avec votre action formualire choisie.';
            } else
                return 'Le nom de l\'action formulaire est invalide';
            return false;
        }

        if ($type == 'installation-steps') {
            $message = "la valeur de content est en inadéquation avec la famille choisi";

            if ($request->family === "nature" && $request->content !== collect(json_decode(MetadataModel::where('name', 'app-types')->first()->data))->pluck('libelle')->all()) {
                return $message;
            }
            if ($request->family === "register-form") {
                $forms_name = collect(json_decode(MetadataModel::where('name', 'forms')->first()->data))->pluck('name')->all();
                $forms_registered_in_installation_names = collect(json_decode(MetadataModel::where('name', 'installation-steps')->first()->data))->where('family', '=', 'register-form')->pluck('content.name')->all();
                if (!isset($request->content["name"]) || !in_array($request->content["name"], $forms_name) || in_array($request->content["name"], $forms_registered_in_installation_names)) {
                    return $message;
                }
            }
            if ($request->family === "register-header") {
                $headers_name = collect(json_decode(MetadataModel::where('name', 'headers')->first()->data))->pluck('name')->all();
                $headers_registered_in_installation_names = collect(json_decode(MetadataModel::where('name', 'installation-steps')->first()->data))->where('family', '=', 'register-header')->pluck('content.name')->all();
                if (!isset($request->content["name"]) || !in_array($request->content["name"], $headers_name) || in_array($request->content["name"], $headers_registered_in_installation_names)) {
                    return $message;
                }
            }
            return false;
        }

        return false;
    }

    protected function getDeleteData($datas, $key)
    {
        $data_update = [];
        foreach ($datas as $key_1 => $value) {
            if ($key != $key_1)
                $data_update[] = $value;
        }
        return $data_update;
    }


    protected function getDeleteDataFroms($datas, $key)
    {
        $data_update = [];
        foreach ($datas as $key_1 => $value) {
            if ($key != $key_1)
                $data_update[] = $value;
        }
        if (empty($data_update))
            return false;
        return $data_update;
    }

    protected function getCreateDataForm($datas_forms, $key, $request)
    {
        $data_update = [];
        foreach ($datas_forms as $key_1 => $value) {
            if ($key == $key_1)
                $data_update[] = array(
                    "name" => $value->name,
                    "description" => $value->description,
                    "content_default" => $value->content_default,
                    "content" => $request->content_default
                );

            else
                $data_update[] = $value;
        }
        if (empty($data_update))
            return false;
        return $data_update;
    }

    protected function getCreateDataHeader($datas_forms,$key,$request){
        $data_update = [];
        foreach ($datas_forms as $key_1 => $value){
            if($key == $key_1)
                $data_update[] = array(
                    "name" => $value->name,
                    "description" => $value->description,
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

    protected function getAllData($datas)
    {
        foreach ($datas as $key => $value) {
            if (!empty($value->content))
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'content' => $value->content
                );
        }
        if (empty($response_data))
            return false;
        return $response_data;
    }

    protected function getAllDataMeta($datas, $type)
    {

        if ($type == 'models') {
            foreach ($datas as $key => $value) {
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'fonction' => $value->fonction
                );
            }
        }

        if ($type == 'forms') {
            foreach ($datas as $key => $value) {
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'content_default' => $value->content_default
                );
            }
        }

        if ($type == 'action-forms') {
            foreach ($datas as $key => $value) {
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                    'endpoint' => $value->endpoint
                );
            }
        }

        if ($type == 'headers') {
            foreach ($datas as $key => $value) {
                $response_data[] = array(
                    'name' => $value->name,
                    'description' => $value->description,
                );
            }
        }

        if ($type == 'installation-steps') {
            foreach ($datas as $key => $value) {
                $response_data[] = array(
                    'family' => $value->family,
                    'title' => $value->title,
                    'content' => $value->content,
                    'name' => $value->name
                );
            }
        }

        if (empty($response_data))
            return false;
        return $response_data;
    }

    protected function getAllDataByTypes($types)
    {
        return MetadataModel::query()
            ->whereIn("name",$types)
            ->get();
    }

    protected function getAllDataProxyByTypes($types)
    {
        return MetadataModel::query()
            ->where("name",$types)
            ->first();
    }

    protected function formatReportTitleMetas($types)
    {
        $metas = $this->getAllDataByTypes($types);
        $response = [];

        foreach ($metas as $meta){
            $title = json_decode($meta->data)->title;
            $description = json_decode($meta->data)->description;
            $name = $meta->name;
            array_push($response,[
                "id"=>$meta->id,
                "name"=>$name,
                "title"=>$title,
                "description"=>$description,
            ]);
        }
        return$response;
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

    /**
     *
     */
    public function insertFormulaire()
    {

    }

    /**
     * @param $name
     * @return mixed
     */
    public function getMetadataByName($name)
    {
       $metadataRepository = new MetadataRepository(new MetadataModel());
       return json_decode($metadataRepository->getByName($name)->data);
    }
}
