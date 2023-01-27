<?php
namespace Satis2020\MetadataPackage\Http\Controllers\Formulaire;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\MetadataPackage\Http\Resources\Formulaire as FormulaireResource;
use Satis2020\MetadataPackage\Http\Resources\ReadFormBuilder as ReadFormBuilderResource;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTraits;

class FormulaireController extends ApiController
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
        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $type = $formulaires->name;
        $formulaire = json_decode($formulaires->data);

        if(empty($formulaire))
            return $this->errorResponse('Aucune valeur métadata formualire trouvée.',422);
        $datas = $this->getAllData($formulaire);
        if(false==$datas)
            return $this->errorResponse('Aucune valeur métadata formualire trouvée.',422);
        return (new FormulaireResource(collect($datas)))->all();
    }

    /**
     * Display a create of the resource.
     *
     * @param name $name
     * @return ReadFormBuilderResource
     * @throws ValidationException
     */
    public function create($name){
        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $models = Metadata::where('name','models')->where('data','!=', '')->firstOrFail();
        $actions = Metadata::where('name','action-forms')->where('data','!=', '')->firstOrFail();
        $action = json_decode($actions->data);
        $model = json_decode($models->data);
        $formulaire = json_decode($formulaires->data);
        $type = $formulaires->name;

        $data = $this->getOneData($formulaire, $name);
        if(false==$data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);
        if(!empty($data['value']->content))
            return $this->errorResponse('Impossible de créer ce métadata "'.$type.'" car son content n\'est pas vide.',422);

        $form_create = [
            'name' => $data['value']->name,
            'description' => $data['value']->description,
            'content_default' => $data['value']->content_default,
            'models' => $model,
            'actions' => $action,
        ];
        return new ReadFormBuilderResource($form_create);
    }


    /**
     * @param $name
     * @return FormulaireResource
     */
    public function show($name){

        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $type = $formulaires->name;
        $formulaire = json_decode($formulaires->data);
        $data = $this->getOneData($formulaire, $name);
        if(false==$data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible d\'accéder aux métadata "'.$type.'" car son content est vide.',422);

        return new FormulaireResource($data['value']);
    }


    /**
     * @param $name
     * @return ReadFormBuilderResource
     */
    public function edit($name)
    {
        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $models = Metadata::where('name','models')->where('data','!=', '')->firstOrFail();
        $actions = Metadata::where('name','action-forms')->where('data','!=', '')->firstOrFail();
        $action = json_decode($actions->data);
        $model = json_decode($models->data);
        $formulaire = json_decode($formulaires->data);
        $type = $formulaires->name;
        $data = $this->getOneData($formulaire, $name);

        if(false==$data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible d\'éditer ce métadata "'.$type.'" car son content n\'est pas encore créé.',422);

        $form_create = [
            'name' => $data['value']->name,
            'description' => $data['value']->description,
            'content' => $data['value']->content,
            'models' => $model,
            'actions' => $action,
        ];
        return new ReadFormBuilderResource($form_create);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return FormulaireResource
     * @throws ValidationException
     */

    public function store(Request $request){
        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $models = Metadata::where('name', 'models')->where('data','!=', '')->firstOrFail();
        $actions = Metadata::where('name', 'action-forms')->where('data','!=', '')->firstOrFail();
        $formulaire = json_decode($formulaires->data);
        $model = json_decode($models->data);
        $action = json_decode($actions->data);

        $type = $formulaires->name;
        $rules = $this->rulesStoreDescription('formulaire');
        $this->validate($request, $rules);

        $validOther = $this->validateOthersMeta($request, $type);
        if(false!=$validOther)
            return $this->errorResponse($validOther, 422);

        $data = $this->getOneData($formulaire, $request->name);
        if(false==$data)
            return $this->errorResponse('La description de ce formulaire n\'existe pas.',422);
        $key = $data['key'];
        if(!empty($data['value']->content))
            return $this->errorResponse('Impossible de créer ce métadata "'.$type.'" car son content n\'est pas vide.',422);
        $data_update = $this->getCreateDataForm($formulaire, $key,$request);
        $update = json_encode($data_update);
        $formulaires->update(['data'=> $update]);
        $data_response = $this->getOneData(json_decode($update), $request->name);
        if(false==$data_response)
            return $this->errorResponse('Aucune valeur métadata formualire trouvée.',422);
        return new FormulaireResource($data_response['value']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $name
     * @return FormulaireResource
     * @throws ValidationException
     */
    public function update(Request $request, $name){
        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $type = $formulaires->name;
        $formulaire = json_decode($formulaires->data);
        $rules = $this->rulesStoreDescription($type);
        $this->validate($request, $rules);
        $data = $this->getOneData($formulaire, $name);
        if(false==$data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);
        $key = $data['key'];
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible de modifier ce métadata "'.$type.'" car son content est vide.',422);

        $data_update = $this->getCreateDataForm($formulaire, $key,$request);
        $update = json_encode($data_update);
        $formulaires->update(['data'=> $update]);
        $formulaire_update = json_decode($formulaires->data);
        $data_response = $this->getOneData($formulaire_update, $request->name);
        return new FormulaireResource($data_response['value']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Metadata $metadata
     * @param $name
     * @return FormulaireResource
     */
    public function destroy($name){
        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $formulaire = json_decode($formulaires->data);
        $type = $formulaires->name;
        $data = $this->getOneData($formulaire, $name);
        if(false == $data)
            return $this->errorResponse('Aucune données métadata "'.$type.'" n\'est disponible.',422);

        $key = $data['key'];
        if(empty($data['value']->content))
            return $this->errorResponse('Impossible de supprimer ce métadata "'.$type.'" car son content est vide.',422);

        $data_update = $this->getDeleteDataFroms($formulaire,$key);
        $update = json_encode($data_update);
        $formulaires->data = $update;
        $formulaires->save();
        return new FormulaireResource($data['value']);
    }

}
