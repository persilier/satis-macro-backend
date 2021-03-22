<?php

namespace Satis2020\UemoaReportsWithoutClient\Http\Controllers\StateOutTime;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\UemoaReports;


/**
 * Class StateMore30DaysController
 * @package Satis2020\UemoaReportsWithoutClient\Http\Controllers\StateMore30Days
 */
class StateOutTimeController extends ApiController
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

        $this->validate($request, $this->ruleFilter($request, false, false, true));

        $claims = $this->resultatsStateOutTime($request, false , false, false, true);

        return response()->json($claims, 200);

    }


    /**
     * @param Request $request
     * @return
     * @throws \Illuminate\Validation\ValidationException
     */
    public function excelExport(Request $request){

        $this->validate($request, $this->ruleFilter($request, false, false, true));

        $claims = $this->resultatsStateOutTime($request, false , false, false, true);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        Excel::store(new StateReportExcel($claims, false, $libellePeriode, 'Réclamations en retard', true), 'rapport-uemoa-etat-hors-delai-any-institution.xlsx');

        return response()->json(['file' => 'rapport-uemoa-etat-hors-delai-any-institution.xlsx'], 200);
    }

}
