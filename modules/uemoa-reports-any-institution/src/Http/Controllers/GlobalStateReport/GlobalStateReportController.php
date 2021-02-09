<?php

namespace Satis2020\UemoaReportsAnyInstitution\Http\Controllers\GlobalStateReport;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class GlobalStateReportController
 * @package Satis2020\UemoaReportsAnyInstitution\Http\Controllers\GlobalStateReport
 */
class GlobalStateReportController extends ApiController
{
    use UemoaReports;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-any-institution')->only(['index', 'excelExport']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsGlobalState($request);

        return response()->json($claims, 200);

    }


    /**
     * @param Request $request
     * @return
     * @throws \Illuminate\Validation\ValidationException
     */
    public function excelExport(Request $request){

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsGlobalState($request);

        return Excel::download(new StateReportExcel($claims, false, false), 'rapport-uemoa-etat-global-reclamation.xlsx');
    }

}
