<?php

namespace Satis2020\AnyInstitutionUnit\Http\Controllers\unit;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Staff;
class UnitStaffController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:update-any-unit')->only(['index']);

    }

    /**
     * Display a listing of the resource.
     *
     * @param $institution
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($institution)
    {
        return response()->json(Staff::with('identite')->where('institution_id', $institution)->get(), 200);
    }

}
