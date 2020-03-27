<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Metadata;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
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
        if(empty($data))
            return $this->errorResponse('Aucune valeur métadata modèle n\'existe.',422);
        return $this->showAll(collect($data));
    }

    /**
     * Display a listing of the resource.
     *
     * @param Metadata $metadata
     * @param Request $request
     * @return Response
     */

    public function store(Metadata $metadata, Request $request){
        $data = json_decode($metadata->data); 
        if($metadata->name == 'models'){
            $rules = Config::get('metadata.models.rules');
            $this->validate($request, $rules);

            if(!empty($data)){
                $names = Arr::pluck($data,Config::get('metadata.models.isValid'));
                if(in_array($request->name, $names))
                    return $this->errorResponse('Veuillez spécifier une valeur métadata modèle name qui n\'existe pas',422);
            }

            $data[] = array(
                'name'=> $request->name,
                'description'=> $request->description,
                'fonction'=> $request->fonction
            );
        }
    
        $metadata->data = json_encode($data);
        $metadata->save();
        $data = json_decode($metadata->data);
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
        if($metadata->name=='models'){
            if(is_null($models))
                return $this->errorResponse('La valeur name du métadata modèle n\'exsite pas.',422); 
            $collection = collect($models);
            $filtered = $collection->firstWhere(Config::get('metadata.models.isValid'), $data);
            if(is_null($filtered))
                return $this->errorResponse('La valeur name du métadata modèle n\'exsite pas.',422);
            $model = collect([
                "name" => $filtered->name,
                "description" => $filtered->name,
                "fonction" => $filtered->fonction
            ]);
        }
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
        if($metadata->name =='models'){
            if(is_null($models))
                return $this->errorResponse('La valeur name du métadata modèle n\'exsite pas',422);
            $collection = collect($models);
            $filtered = $collection->firstWhere(Config::get('metadata.models.isValid'), $data);
            if(is_null($filtered))
                return $this->errorResponse('La valeur name du métadata modèle n\'exsite pas',422);
            foreach ($models as $key => $value){
                if($value->name != $filtered->name){
                    $model[] = array(
                        'name'=> $value->name,
                        'description'=> $value->description,
                        'fonction'=> $value->fonction
                    );
                }
            }
        }
        $metadata->data = json_encode($model);
        $metadata->save();
        return $this->showAll(collect($filtered));

    }

}

