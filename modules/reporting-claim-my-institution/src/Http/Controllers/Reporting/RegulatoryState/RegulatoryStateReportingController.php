<?php

namespace Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting\RegulatoryState;


use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\RegulatoryStateReportingRequest;
use Satis2020\ServicePackage\Services\Reporting\RegulatoryState\RegulatoryStateService;
use Satis2020\ServicePackage\Traits\UemoaReports;

class RegulatoryStateReportingController extends ApiController
{

    use UemoaReports;

    /**
     * StateReportingController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-regulatory-reporting-claim-my-institution')->only(['index']);
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

        /*$logo = $this->logo($this->institution());
        $colorTableHeader = $this->colorTableHeader();
        $logoSatis = asset('assets/reporting/images/satisLogo.png');

        $view = view('ServicePackage::reporting.pdf-regulatory-state-reporting', compact("data","logo","logoSatis","colorTableHeader"))->render();

        $file = 'rapport-uemoa-etat-global-reclamation-my-institution.pdf';

        $pdf = App::make('dompdf.wrapper');

        $pdf->loadHTML($view);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($file);*/

        return response()->json($data, 200);
    }
}