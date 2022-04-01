<?php

namespace Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting\RegulatoryState;


use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\RegulatoryStateReportingRequest;
use Satis2020\ServicePackage\Services\Reporting\RegulatoryState\RegulatoryStateService;

class RegulatoryStateReportingController extends ApiController
{


    /**
     * StateReportingController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-my-institution')->only(['index']);
    }

    /**
     * @param RegulatoryStateReportingRequest $request
     * @param RegulatoryStateService $service
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index(RegulatoryStateReportingRequest $request,RegulatoryStateService $service)
    {
        $request->merge([
            "institution_id"=>$this->institution()->id
        ]);

        $data = $service->generateReport($request);


        $data = view('ServicePackage::uemoa.report-reclamation', [
            'claims' => $claims,
            'myInstitution' => true,
            'libellePeriode' => $libellePeriode,
            'title' => 'Rapport global des réclamations',
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

        return response()->json($data, 200);
    }
}