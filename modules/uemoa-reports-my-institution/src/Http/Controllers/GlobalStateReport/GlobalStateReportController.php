<?php

namespace Satis2020\UemoaReportsMyInstitution\Http\Controllers\GlobalStateReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exports\UemoaReports\GlobalStateReportExcel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class GlobalStateReportController
 * @package Satis2020\UemoaReportsMyInstitution\Http\Controllers\GlobalStateReport
 */
class GlobalStateReportController extends ApiController
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
        
        $claims = $this->resultatsGlobalState($request, true);

        return response()->json($claims, 200);

    }

    /**
     * @param Request $request
     * @return
     * @throws \Illuminate\Validation\ValidationException
     */
    public function excelExport(Request $request){

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsGlobalState($request, true, true, false, true, true );

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $titleDescription = $this->getMetadataByName(Constants::GLOBAL_STATE_REPORTING)->title.' : '.$this->getMetadataByName(Constants::GLOBAL_STATE_REPORTING)->description;
       //dd($claims);
        Excel::store(new StateReportExcel($claims, true, $libellePeriode, $titleDescription, false), 'rapport-uemoa-etat-global-reclamation-my-institution.xlsx');


        return response()->json(['file' => 'rapport-uemoa-etat-global-reclamation-my-institution.xlsx'], 200);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function pdfExport(Request $request){

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsGlobalState($request, true);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $data = view('ServicePackage::uemoa.report-reclamation', [
            'claims' => $claims,
            'myInstitution' => true,
            'libellePeriode' => $libellePeriode,
            'title' => $this->getMetadataByName(Constants::GLOBAL_STATE_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::GLOBAL_STATE_REPORTING)->description,
            'relationShip' => false,
            'logo' => $this->logo($this->institution()),
            'colorTableHeader' => $this->colorTableHeader(),
            'logoSatis' => asset('assets/reporting/images/satisLogo.png'),
        ])->render();

        $file = 'rapport-uemoa-etat-global-reclamation-my-institution.pdf';

        $pdf = App::make('dompdf.wrapper');

        $pdf->loadHTML($data);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($file);
    }

}
