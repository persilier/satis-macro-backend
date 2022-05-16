<?php

namespace Satis2020\NotificationProof\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\NotificationProofRequest;

use Satis2020\ServicePackage\Services\NotificationProof\NotificationProofService;
use Satis2020\ServicePackage\Traits\ActivePilot;

class IndependantNotificationProofController extends ApiController
{
    use ActivePilot;


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
        if(Auth::check() &&  $this->checkIfStaffIsPilot($this->staff())) {
            $this->middleware('permission:pilot-list-notification-proof')->only(['index']);
            $this->middleware('active.pilot')->only(['index']);
        }else{
            $this->middleware('permission:list-notification-proof')->only(['index']);
        }

    }

    /**
     * @param NotificationProofRequest $request
     * @param int $pagination
     * @return mixed
     * @throws RetrieveDataUserNatureException
     */
    public function index(NotificationProofRequest $request,$pagination=NotificationConsts::PAGINATION_LIMIT)
    {
        return response($this->notificationProofService->filterInstitutionNotificationProofs($this->institution()->id,$request,$pagination),Response::HTTP_OK);
    }


}