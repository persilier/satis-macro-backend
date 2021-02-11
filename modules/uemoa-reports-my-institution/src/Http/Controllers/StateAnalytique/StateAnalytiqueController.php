<?php

namespace Satis2020\UemoaReportsMyInstitution\Http\Controllers\StateAnalytique;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateAnalytiqueReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class StateAnalytiqueController
 * @package Satis2020\UemoaReportsMyInstitution\Http\Controllers\StateAnalytique
 */
class StateAnalytiqueController extends ApiController
{
    use UemoaReports;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-my-institution')->only(['index', 'excelExport']);
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

        $claims = $this->resultatsStateAnalytique($request, true);

        return response()->json($claims, 200);

    }


    /**
     * @param Request $request
     * @return
     * @throws \Illuminate\Validation\ValidationException
     */
    public function excelExport(Request $request)
    {

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsStateAnalytique($request, true);

        Excel::store(new StateAnalytiqueReportExcel($claims, true), 'rapport-uemoa-etat-analytique-my-institution.xlsx');

        return response()->json(['file' => 'rapport-uemoa-etat-analytique-my-institution.xlsx'], 200);
    }

}
