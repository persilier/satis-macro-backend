<?php

namespace Satis2020\InstitutionPackage\Http\Controllers\Institutions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\UploadFile;
use Satis2020\InstitutionPackage\Http\Resources\Institution as InstitutionResource;
use Satis2020\InstitutionPackage\Http\Resources\InstitutionCollection;

class InstitutionPositionUnitController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Institution $institution
     * @return InstitutionCollection
     */
    public function index(Institution $institution)
    {
        $institution->load(['positions', 'units']);
        return response()->json($institution->only(['positions', 'units']), 200);
    }

}
