<?php

namespace Satis2020\UemoaReportsWithoutClient\Http\Controllers\GlobalStateReport;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class GlobalStateReportController
 * @package Satis2020\UemoaReportsWithoutClient\Http\Controllers\GlobalStateReport
 */
class GlobalStateReportController extends ApiController
{
    use UemoaReports, CreateClaim;

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

        $this->validate($request, $this->ruleFilter($request, false, true, false));

        $claims = $this->resultatsGlobalState($request, false, false, true, false);

        return response()->json($claims, 200);

    }

    /**
     * @param Request $request
     * @return
     * @throws \Illuminate\Validation\ValidationException
     */
    public function excelExport(Request $request){

        $this->validate($request, $this->ruleFilter($request, false, true, false));

        $claims = $this->resultatsGlobalState($request, false,false, true, false);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        Excel::store(new StateReportExcel($claims, false, $libellePeriode, 'Rapport global des réclamations', true), 'rapport-uemoa-etat-global-reclamation-any-institution.xlsx');

        return response()->json(['file' => 'rapport-uemoa-etat-global-reclamation-any-institution.xlsx'], 200);
    }

}
