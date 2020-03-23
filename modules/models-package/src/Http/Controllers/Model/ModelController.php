<?php

namespace Satis2020\ModelPackage\Http\Controllers\Model;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

class ModelController extends ApiController
{

    /**
     * ModelController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $models = Metadata::where('name', 'models')->get()->first();
        return $this->showOne($models);
    }

    public function store(){
        
    }

    public function show($name){
        $model = Metadata::where('name','models')->get()->first();
        $data = json_decode($model);

    }


}

