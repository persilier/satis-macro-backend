<?php

namespace Satis2020\NotificationProof\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Repositories\InstitutionRepository;
use Satis2020\ServicePackage\Requests\NotificationProofRequest;

use Satis2020\ServicePackage\Services\NotificationProof\NotificationProofService;
use Satis2020\ServicePackage\Traits\ActivePilot;

class NotificationProofController extends ApiController
{
    use ActivePilot;

    /**
     * @var NotificationProofService
     */
    private $notificationProofService;

    /**
     * AuthConfigController constructor.
     * @param NotificationProofService $proofService
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function __construct(NotificationProofService $proofService)
    {
        parent::__construct();
        $this->notificationProofService = $proofService;
        $this->middleware('auth:api');
        if(Auth::check() && $this->checkIfStaffIsPilot($this->staff())) {
            $this->middleware('permission:pilot-list-any-notification-proof')->only(['index','create']);
            $this->middleware('active.pilot')->only(['index']);
        }else{
            $this->middleware('permission:list-any-notification-proof')->only(['index','create']);
        }
    }

    /**
     * @param NotificationProofRequest $request
     * @param int $pagination
     * @return mixed
     */
    public function index(NotificationProofRequest $request,$pagination=NotificationConsts::PAGINATION_LIMIT)
    {
        $institutionRepository = app(InstitutionRepository::class);

        if($this->checkIfStaffIsPilot($this->staff()))
        {
            if (!$this->staff()->is_active_pilot) {
                return $this->errorResponse('L\'utilisateur n\'a pas la bonne autorisation', 401);
            }else{
                return response([
                        "proofs"=>$this->notificationProofService->filterNotificationProofs($request,$pagination),
                        "filter-data"=>$institutionRepository->getAll()]
                    ,Response::HTTP_OK);
            }
        }else{
            return response([
                    "proofs"=>$this->notificationProofService->filterNotificationProofs($request,$pagination),
                    "filter-data"=>$institutionRepository->getAll()]
                ,Response::HTTP_OK);
        }
    }

    /**
     *
     */
    public function create()
    {
        $institutionRepository = app(InstitutionRepository::class);

        if($this->checkIfStaffIsPilot($this->staff())) {
            if (!$this->staff()->is_active_pilot) {
                return $this->errorResponse('L\'utilisateur n\'a pas la bonne autorisation', 401);
            } else {
                return \response($institutionRepository->getAll(),Response::HTTP_OK);
            }
        }else{
            return \response($institutionRepository->getAll(),Response::HTTP_OK);
        }
    }




}