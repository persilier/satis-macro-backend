<?php

namespace Satis2020\UemoaReportsAnyInstitution\Http\Controllers\StateAnalytique;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateAnalytiqueReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class StateAnalytiqueController
 * @package Satis2020\UemoaReportsAnyInstitution\Http\Controllers\StateAnalytique
 */
class StateAnalytiqueController extends ApiController
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

        $claims = $this->resultatsStateAnalytique($request);

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

        $claims = $this->resultatsStateAnalytique($request);

        Excel::store(new StateAnalytiqueReportExcel($claims, false), 'rapport-uemoa-etat-analytique-any-institution.xlsx');

        return response()->json(['file' => storage_path('app\rapport-uemoa-etat-analytique-any-institution.xlsx')], 200);
    }

}
