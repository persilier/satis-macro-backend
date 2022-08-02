<?php

namespace Satis2020\UemoaReportsAnyInstitution\Http\Controllers\Institution;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\UemoaReports;


/**
 * Class StateMore30DaysController
 * @package Satis2020\UemoaReportsAnyInstitution\Http\Controllers\InstitutionController
 */
class InstitutionController extends ApiController
{


    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-any-institution')->only(['index', 'excelExport']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index()
    {

        return response()->json(Institution::whereHas('institutionType', function ($q){

            $q->where('maximum_number_of_institutions', 0);

        })->get(), 200);

    }

}
