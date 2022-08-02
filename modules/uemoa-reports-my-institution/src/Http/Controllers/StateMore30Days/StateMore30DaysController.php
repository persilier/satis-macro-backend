<?php

namespace Satis2020\UemoaReportsMyInstitution\Http\Controllers\StateMore30Days;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\UemoaReports;


/**
 * Class StateMore30DaysController
 * @package Satis2020\UemoaReportsMyInstitution\Http\Controllers\StateMore30Days
 */
class StateMore30DaysController extends ApiController
{
    use UemoaReports,Metadata;

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

        $claims = $this->resultatsStateMore30Days($request, true);

        return response()->json($claims, 200);

    }


    /**
     * @param Request $request
     * @return
     * @throws \Illuminate\Validation\ValidationException
     */
    public function excelExport(Request $request){

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsStateMore30Days($request, true);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $titleDescription = $this->getMetadataByName(Constants::OUT_OF_30_DAYS_REPORTING)->title.' : '.$this->getMetadataByName(Constants::OUT_OF_30_DAYS_REPORTING)->description;

        Excel::store(new StateReportExcel($claims, true, $libellePeriode, $titleDescription, false), 'rapport-uemoa-etat-reclamation-30-jours-my-institution.xlsx');

        return response()->json(['file' => 'rapport-uemoa-etat-reclamation-30-jours-my-institution.xlsx'], 200);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function pdfExport(Request $request){

        $this->validate($request, $this->ruleFilter($request));

        $claims = $this->resultatsStateMore30Days($request);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $data = view('ServicePackage::uemoa.report-reclamation', [
            'claims' => $claims,
            'myInstitution' => true,
            'libellePeriode' => $libellePeriode,
            'title' => $this->getMetadataByName(Constants::OUT_OF_30_DAYS_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::OUT_OF_30_DAYS_REPORTING)->description,
            'relationShip' => false,
            'logo' => $this->logo($this->institution()),
            'colorTableHeader' => $this->colorTableHeader(),
            'logoSatis' => asset('assets/reporting/images/satisLogo.png'),
        ])->render();

        $file = 'rapport-uemoa-etat-reclamation-30-jours-my-institution.pdf';

        $pdf = App::make('dompdf.wrapper');

        $pdf->loadHTML($data);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($file);
    }

}
