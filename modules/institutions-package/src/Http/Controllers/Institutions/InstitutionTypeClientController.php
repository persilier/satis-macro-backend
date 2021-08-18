<?php

namespace Satis2020\InstitutionPackage\Http\Controllers\Institutions;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ClientPackage\Http\Resources\TypeClientCollection;
class InstitutionTypeClientController extends ApiController
{
    /**
     * InstitutionTypeClientController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @param String $slug
     * @return TypeClientCollection
     */
    public function index($slug)
    {
        $institution = Institution::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();
        return new TypeClientCollection($institution->type_clients);
    }

}
