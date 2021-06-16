<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Metadata;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\MetadataPackage\Http\Resources\Metadata as MetadataResource;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTraits;

class MetadataController extends ApiController
{
    use MetadataTraits;

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
     * @return MetadataResourceCollection
     */
    public function index(Metadata $metadata)
    {
        $data = json_decode($metadata->data);
        $type = $metadata->name;
        if (empty($data))
            return $this->errorResponse('Aucune valeur métadata ' . $type . ' trouvée.', 422);
        $datas = $this->getAllDataMeta($data, $type);
        return (new MetadataResource(collect($datas), $type))->all();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Metadata $metadata
     * @param Request $request
     * @return MetadataResource
     * @throws ValidationException
     */

    public function store(Metadata $metadata, Request $request)
    {
        $type = $metadata->name;
        $rules = $this->rulesStoreDescription($type);
        $this->validate($request, $rules);

        $model = $this->validateOthersMeta($request, $type);
        if (false != $model)
            return $this->errorResponse($model, 422);

        if (!in_array($type, ["app-nature"])) {
            $datas = json_decode($metadata->data);
            $data = $this->getOneData($datas, $request->name);
            if (false != $data)
                return $this->errorResponse('Le name de cette description existe déjà pour les métadata "' . $type . '".', 422);
        }

        if (!$fillable_metat = $this->fillable_meta($type, $request))
            return $this->errorResponse('Veuillez configurer les champs fillable des métadata "' . $type . '".', 422);

        $datas[] = (object)$fillable_metat;
        $data_update = json_encode($datas);
        $metadata->update(['data' => $data_update]);
        return new MetadataResource((object)$fillable_metat, $type);
    }

    /**
     * Display the specified resource.
     *
     * @param Metadata $metadata
     * @param $name
     * @return MetadataResource
     */
    public function show(Metadata $metadata, $name)
    {
        $type = $metadata->name;
        $datas = json_decode($metadata->data);
        $data = $this->getOneData($datas, $name);
        if (false == $data)
            return $this->errorResponse('Aucune données métadata "' . $type . '" n\'est disponible.', 422);
        return new MetadataResource($data['value'], $type);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Metadata $metadata
     * @param Request $request
     * @param $name
     * @return MetadataResource
     * @throws ValidationException
     */
    public function update(Metadata $metadata, Request $request, $name)
    {
        $rules = $this->rulesUpdateDescription();
        $this->validate($request, $rules);
        $type = $metadata->name;
        $datas = json_decode($metadata->data);
        $data = $this->getOneData($datas, $name);
        if (false == $data)
            return $this->errorResponse('Cette valeur de métadata "' . $type . '" n\'existe pas.', 422);
        $key = $data['key'];
        $data['value']->description = $request->description;
        $datas[$key]->description = $request->description;
        $data_update = json_encode($datas);
        $metadata->update(['data' => $data_update]);
        return new MetadataResource($data['value'], $type);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Metadata $metadata
     * @param $name
     * @return MetadataResource
     */
    public function destroy(Metadata $metadata, $name)
    {
        $type = $metadata->name;
        $datas = json_decode($metadata->data);
        $data = $this->getOneData($datas, $name);
        if (false == $data)
            return $this->errorResponse('Cette valeur métadata "' . $type . '" n\'est disponible.', 422);
        $key = $data['key'];
        if (!empty($data['value']->content) && ($type == 'forms' || $type == 'headers'))
            return $this->errorResponse('Impossible de supprimer ce métadata "' . $type . '" car son content n\'est pas vide.', 422);

        $data_update = $this->getDeleteData($datas, $key);
        $update = json_encode($data_update);
        $metadata->data = $update;
        $metadata->save();
        return new MetadataResource($data['value'], $type);
    }

}

