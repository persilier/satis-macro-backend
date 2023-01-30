<?php

namespace Satis2020\NotificationProof\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\NotificationProofRequest;

use Satis2020\ServicePackage\Services\NotificationProof\NotificationProofService;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\UemoaReports;

class ExportToPdfIndependantNotificationProofController extends ApiController
{
    use ActivePilot, Metadata, UemoaReports;


    /**
     * @var NotificationProofService
     */
    private $notificationProofService;

    /**
     * AuthConfigController constructor.
     * @param NotificationProofService $proofService
     * @throws RetrieveDataUserNatureException
     */
    public function __construct(NotificationProofService $proofService)
    {
        parent::__construct();
        $this->notificationProofService = $proofService;
        $this->middleware('auth:api');
        if(Auth::check() && $this->checkIfStaffIsPilot($this->staff())) {
            $this->middleware('permission:pilot-export-notification-proof')->only(['index','allProof']);
            $this->middleware('active.pilot')->only(['index','allProof']);
        }else{
            $this->middleware('permission:export-notification-proof')->only(['index','allProof']);
        }

    }

    /**
     * @param NotificationProofRequest $request
     * @return mixed
     */
    public function index(NotificationProofRequest $request)
    {
        $data = $this->allProof($request);

        $pdf = Pdf::loadView('ServicePackage::reporting.pdf-export-notification-proof', compact('data'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('NotificationProof.pdf');
    }

    public function allProof($request){
      $proof = $this->notificationProofService->filterInstitutionNotificationProofTtoExport($this->institution()->id,$request)->toArray();
      $period = \Carbon\Carbon::parse($request->date_start)->format('d M Y') . ' au '. \Carbon\Carbon::parse($request->date_end)->format('d M Y');

      return [
          'institution' => $this->institution(),
          'logo'=> $this->logo($this->institution()),
          'title' => $this->getMetadataByName(Constants::NOTIFICATION_PROOF)->title,
          'description' => $this->getMetadataByName(Constants::NOTIFICATION_PROOF)->description,
          'colorTableHeader' => $this->colorTableHeader(),
          'libellePeriode' => $period,
          'report_title' => $this->getMetadataByName(Constants::NOTIFICATION_PROOF)->title,
          'proof' => $proof
      ];
    }


}