<?php

namespace Satis2020\UemoaReportsAnyInstitution\Http\Controllers\StateAnalytique;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateAnalytiqueReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
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
     * @throws ValidationException
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
     * @throws ValidationException
     */
    public function excelExport(Request $request)
    {

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsStateAnalytique($request);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        Excel::store(new StateAnalytiqueReportExcel($claims, false, $libellePeriode), 'rapport-uemoa-etat-analytique-any-institution.xlsx');

        return response()->json(['file' => 'rapport-uemoa-etat-analytique-any-institution.xlsx'], 200);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     * @throws \Throwable
     */
    public function pdfExport(Request $request)
    {

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsStateAnalytique($request);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $data = view('ServicePackage::uemoa.report-analytique', [
            'claims' => $claims,
            'myInstitution' => false,
            'description' => null,
            'libellePeriode' => $libellePeriode,
            'title' => 'Rapport Analytique',
            'logo' => $this->logo($this->institution()),
            'colorTableHeader' => $this->colorTableHeader(),
            'logoSatis' => asset('assets/reporting/images/satisLogo.png'),
        ])->render();

        $file = 'rapport-uemoa-etat-analytique-any-institution.pdf';

        $pdf = App::make('dompdf.wrapper');

        $pdf->loadHTML($data);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($file);
    }

}
