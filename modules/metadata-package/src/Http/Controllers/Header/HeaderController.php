<?php
namespace Satis2020\MetadataPackage\Http\Controllers\Header;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\MetadataPackage\Http\Resources\Metadata as MetadataResource;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\MetadataPackage\Http\Resources\Header as HeaderResource;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTraits;

class HeaderController extends ApiController
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
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $headers = Metadata::where('name', 'headers')->where('data','!=', '')->firstOrFail();
        $type = $headers->name;
        $header = json_decode($headers->data);

        if(empty($header))
            return $this->errorResponse('Aucune valeur métadata header trouvée.',422);
        $datas = $this->getAllData($header);
        if(false==$datas)
            return $this->errorResponse('Aucune valeur métadata header trouvée.',422);
        return (new HeaderResource(collect($datas)))->all();
    }

    /**
     * Display a create of the resource.
     *
     * @param name $name
     * @return MetadataResource
     * @throws ValidationException
     */
    public function create($name){
        $headers = Metadata::where('name', 'headers')->where('data','!=', '')->firstOrFail();
        $header = json_decode($headers->data);
        $type = $headers->name;

        $data = $this->getOneData($header, $name);
        if(false==$data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);
        if(!empty($data['value']->content))
            return $this->errorResponse('Impossible de créer ce métadata "'.$type.'" car son content n\'est pas vide.',422);

        $header_create = [
            'name' => $data['value']->name,
            'description' => $data['value']->description,
        ];
        return new MetadataResource((object) $header_create, $type);
        //return new HeaderResource($header_create);
    }


    /**
     * @param $name
     * @return HeaderResource
     */
    public function show($name){

        $headers = Metadata::where('name', 'headers')->where('data','!=', '')->firstOrFail();
        $type = $headers->name;
        $header = json_decode($headers->data);
        $data = $this->getOneData($header, $name);
        if(false==$data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible d\'accéder aux métadata "'.$type.'" car son content est vide.',422);

        return new HeaderResource($data['value']);
    }


    /**
     * @param $name
     * @return HeaderResource
     */
    public function edit($name)
    {
        $headers = Metadata::where('name', 'headers')->where('data','!=', '')->firstOrFail();
        $header = json_decode($headers->data);
        $type = $headers->name;
        $data = $this->getOneData($header, $name);

        if(false==$data)
            return $this->errorResponse('Aucune donnée header n\'est disponible.',422);
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible d\'accéder à ce header car son content n\'est pas encore créé.',422);

        $header_create = [
            'name' => $data['value']->name,
            'description' => $data['value']->description,
            'content' => $data['value']->content,
        ];
        //dd((object) $header_create);
        return new HeaderResource((object) $header_create);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return HeaderResource
     * @throws ValidationException
     */

    public function store(Request $request){
        $headers = Metadata::where('name', 'headers')->where('data','!=', '')->firstOrFail();
        $header = json_decode($headers->data);
        $type = $headers->name;
        $rules = $this->rulesStoreDescription('headers-update');
        $this->validate($request, $rules);

        $validOther = $this->validateOthersMeta($request, $type);
        if(false!=$validOther)
            return $this->errorResponse($validOther, 422);

        $data = $this->getOneData($header, $request->name);
        if(false==$data)
            return $this->errorResponse('La description de ce header n\'existe pas.',422);
        $key = $data['key'];
        if(!empty($data['value']->content))
            return $this->errorResponse('Impossible de créer ce header "'.$type.'" car son content n\'est pas vide.',422);
        $data_update = $this->getCreateDataHeader($header, $key,$request);
        $update = json_encode($data_update);
        $headers->update(['data'=> $update]);
        $data_response = $this->getOneData(json_decode($update), $request->name);
        if(false==$data_response)
            return $this->errorResponse('Aucune valeur de header trouvée.',422);
        return new HeaderResource($data_response['value']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $name
     * @return HeaderResource
     * @throws ValidationException
     */
    public function update(Request $request, $name){
        $headers = Metadata::where('name', 'headers')->where('data','!=', '')->firstOrFail();
        $type = $headers->name;
        $header = json_decode($headers->data);
        $rules = $this->rulesStoreDescription('headers-update');
        $this->validate($request, $rules);
        $data = $this->getOneData($header, $name);
        if(false==$data)
            return $this->errorResponse('Aucune données header n\'est disponible.',422);
        $key = $data['key'];
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible de modifier ce header car son content est vide.',422);

        $data_update = $this->getCreateDataHeader($header, $key,$request);
        $update = json_encode($data_update);
        $headers->update(['data'=> $update]);
        $data_response = $this->getOneData($header, $request->name);
        return new HeaderResource($data_response['value']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Metadata $metadata
     * @param $name
     * @return HeaderResource
     */
    public function destroy($name){
        $headers = Metadata::where('name', 'headers')->where('data','!=', '')->firstOrFail();
        $header = json_decode($headers->data);
        $type = $headers->name;
        $data = $this->getOneData($header, $name);
        if(false == $data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);

        $key = $data['key'];
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible de supprimer ce métadata "'.$type.'" car son content est vide.',422);

        $data_update = $this->getDeleteDataFroms($header,$key);
        $update = json_encode($data_update);
        $headers->data = $update;
        $headers->save();
        return new HeaderResource($data['value']);
    }

}
