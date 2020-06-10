<?php

namespace Satis2020\AnyInstitution\Http\Controllers\Institutions;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
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
        $institution->load('units');
        $units = $institution->units;
        return response()->json([
            'units' => $units,
            'staffs' => Staff::with('identite')->where('institution_id', $institution->id)->get()
        ], 200);
    }

}
