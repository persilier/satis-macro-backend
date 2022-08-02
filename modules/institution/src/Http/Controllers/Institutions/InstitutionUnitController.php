<?php

namespace Satis2020\Institution\Http\Controllers\Institutions;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;

class InstitutionUnitController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Institution $institution)
    {
        $institution->load(['units.lead.identite']);
        return response()->json($institution->only(['units']), 200);
    }

}
