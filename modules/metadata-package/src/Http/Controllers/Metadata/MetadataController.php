<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Metadata;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

class MetadataController extends ApiController
{

    /**
     * MetadataController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Metadata $metadata
     * @return Response
     */
    public function index(Metadata $metadata)
    {
        $data = json_decode($metadata->data);
        $type = $metadata->name;

        if(empty($data))
            return $this->errorResponse('Aucune valeur métadata '.$type.' trouvée.',422);
        return $this->showAll(collect($data));
    }

    /**
     * Display a listing of the resource.
     *
     * @param Metadata $metadata
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */

    public function store(Metadata $metadata, Request $request){

        $data = json_decode($metadata->data);
        $types = Config::get('metadata.type');

        if(empty($types))
            return $this->errorResponse('Variables de configuration de métadata "'.$metadata->name.'" non définies.',422);
        $index = array_search($metadata->name, $types);

        if(false===$index)
            return $this->errorResponse('Variables de configuration de métadata type "'.$metadata->name.'" non définies.',422);

        $type = $types[$index];
        $rules = Config::get('metadata.'.$type.'.rules');

        if(empty($rules))
            return $this->errorResponse('Variables de configuration (Validation) de métadata "'.$metadata->name.'" non définies.',422);

        $this->validate($request, $rules);
        if($type == 'models'){
            if(!is_string($request->fonction)){
                return $this->errorResponse("La valeur de l'attribut n'est une chaine de caractère.",422);
            }
            $tab = explode('::',$request->fonction,2);
            if(count($tab)!=2){
                return $this->errorResponse("Le format de la valeur de l'attribut fonction est invalide.",422);
            }
            // model & method validation
            $model = $tab[0];
            $method = $tab[1];
            // model validation
            if (!class_exists($tab[0])) {
                return $this->errorResponse("cannot determine values for {$model}. The model provided is not a valid class.",422);
            }
            // method validation
            try {
                $reflection_model = new ReflectionClass($model);
                $method = $reflection_model->getMethod($method);
                if (!$method->isStatic()) {
                    return $this->errorResponse("cannot determine values for {$request->fonction}. The method provided is not a valid static method of the model provided class.",422);
                }
            } catch (ReflectionException $exception) {
                return $this->errorResponse("cannot determine values for {$request->fonction}. The method provided is not a valid static method of the model provided class",422);
            }
        }

        if(!empty($data)){
            $names = Arr::pluck($data,Config::get('metadata.'.$type.'.isValid'));
            if(in_array($request->name, $names))
                return $this->errorResponse('Veuillez spécifier une valeur métadata "'.$type.'" name qui n\'existe pas.',422);
        }

        $fillables = Config::get('metadata.'.$type.'.fillable');
        if(empty($fillables))
            return $this->errorResponse('Variables de configuration (Champs d\'ajout) de métadata "'.$metadata->name.'" non définies.',422);

        $data[] = $request->only($fillables);

        $metadata->data = json_encode($data);
        $metadata->save();
        return $this->showAll(collect($request));
    }

    /**
     * Display the specified resource.
     *
     * @param Metadata $metadata
     * @param String $data
     * @return Response
     */
    public function show(Metadata $metadata, $data){
        $models = json_decode($metadata->data);
        $model = array();
        $types = Config::get('metadata.type');
        if(empty($types))
            return $this->errorResponse('Variables de configuration de métadata '.$metadata->name.' non définies.',422);
        $type = $types[array_search($metadata->name, $types)];

        if(is_null($models))
            return $this->errorResponse('Aucune métadata '.$type.' n\'est disponible.',422);

        $collection = collect($models);
        $filtered = $collection->firstWhere(Config::get('metadata.'.$type.'.isValid'), $data);
        if(is_null($filtered))
            return $this->errorResponse('La valeur name du métadata '.$type.' n\'exsite pas.',422);

        $fillables = Config::get('metadata.'.$type.'.fillable');

        if(empty($fillables))
            return $this->errorResponse('Variables de configuration (Champs d\'ajout) de métadata '.$metadata->name.' non définies.',422);

        $model = collect($filtered)->only($fillables);
        return  $this->showAll($model);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param Metadata $metadata
     * @param String $data
     * @return Response
     */
    public function destroy(Metadata $metadata, $data){
        $models = json_decode($metadata->data);
        $model = array();
        $types = Config::get('metadata.type');
        if(empty($types))
            return $this->errorResponse('Variables de configuration de métadata '.$metadata->name.' non définies.',422);
        $type = $types[array_search($metadata->name, $types)];
        if(is_null($models))
            return $this->errorResponse('Aucune métadata '.$type.' n\'est disponible.',422);
        $collection = collect($models);
        $filtered = $collection->firstWhere(Config::get('metadata.'.$type.'.isValid'), $data);
        if(is_null($filtered))
            return $this->errorResponse('La valeur name du métadata '.$type.' n\'exsite pas.',422);

        if(Config::get('metadata.'.$type.'.isNotDelete')){
            if(Arr::exists(collect($filtered), Config::get('metadata.'.$type.'.isNotDelete')))
                return $this->errorResponse('Impossible de supprimer ce métadata '.$type.' car son contenu est déjà configuré. .',422);

        }
        $fillables = Config::get('metadata.'.$type.'.fillable');
        if(empty($fillables))
            return $this->errorResponse('Variables de configuration (Champs d\'ajout) de métadata '.$metadata->name.' non définies.',422);

        foreach ($models as $value){
            if($value->name != $filtered->name){
                $model[] = $value;
            }
        }
        $metadata->data = json_encode($model);
        $metadata->save();
        return $this->showAll(collect($filtered));
    }

}

