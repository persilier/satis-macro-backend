<?php

namespace Satis2020\UemoaReportsAnyInstitution\Http\Controllers\StateMore30Days;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\UemoaReports;
use Throwable;


/**
 * Class StateMore30DaysController
 * @package Satis2020\UemoaReportsAnyInstitution\Http\Controllers\StateMore30Days
 */
class StateMore30DaysController extends ApiController
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
     * @throws ValidationException
     */
    public function index(Request $request)
    {

        $this->validate($request, $this->ruleFilter($request));

        $claims = $this->resultatsStateMore30Days($request);

        return response()->json($claims, 200);

    }


    /**
     * @param Request $request
     * @return
     * @throws ValidationException
     */
    public function excelExport(Request $request){

        $this->validate($request, $this->ruleFilter($request));

        $claims = $this->resultatsStateMore30Days($request);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        Excel::store(new StateReportExcel($claims, false, $libellePeriode, 'Reclamation en retard de +30j', false), 'rapport-uemoa-etat-reclamation-30-jours-any-institution.xlsx');

        return response()->json(['file' => 'rapport-uemoa-etat-reclamation-30-jours-any-institution.xlsx'], 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Throwable
     */
    public function pdfExport(Request $request){

        $this->validate($request, $this->ruleFilter($request));

        $claims = $this->resultatsStateMore30Days($request);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $data = view('ServicePackage::uemoa.report-reclamation', [
            'claims' => $claims,
            'myInstitution' => false,
            'description' => null,
            'libellePeriode' => $libellePeriode,
            'title' => 'Reclamation en retard de +30j',
            'relationShip' => false,
            'logo' => $this->logo($this->institution()),
            'colorTableHeader' => $this->colorTableHeader(),
            'logoSatis' => asset('assets/reporting/images/satisLogo.png'),
        ])->render();

        $file = 'rapport-uemoa-etat-reclamation-30-jours-any-institution.pdf';

        $pdf = App::make('dompdf.wrapper');

        $pdf->loadHTML($data);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($file);
    }

}
