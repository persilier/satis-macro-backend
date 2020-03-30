<?php
namespace Satis2020\MetadataPackage\Http\Controllers\Formulaire;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

class FormulaireController extends ApiController
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
     * @return Response
     */
    public function index()
    {
        $metadata = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $formulaires = json_decode($metadata->data);
        return $this->showAll(collect($formulaires));
    }

    /**
     * Display a create of the resource.
     *
     * @param name $name
     * @return Response
     * @throws ValidationException
     */
    public function create($name){
        $metadata = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $formulaires = json_decode($metadata->data);
        $collection = collect($formulaires);
        $formulaire = $collection->firstWhere('name', $name);
        if(is_null($formulaire))
            return $this->errorResponse('Ce formulaire n\'exsite pas.',422);
        if(Arr::exists(collect($formulaire), 'content'))
            return $this->errorResponse('Ce formulaire a été créé déjà.',422);
        $models = Metadata::where('name','models')->where('data','!=', '')->firstOrFail();
        $actions_forms = Metadata::where('name','action-forms')->where('data','!=', '')->firstOrFail();
        $actions = json_decode($actions_forms->data);
        $models = json_decode($models->data);
        $form_create = [
            'name' => $formulaire->name,
            'description' => $formulaire->description,
            'models' => $models,
            'actions' => $actions,
        ];
        return $this->showAll(collect($form_create));
    }

    public function show(){

    }

    /**
     * Display a listing of the resource.
     *
     * @param Metadata $metadata
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */

}
