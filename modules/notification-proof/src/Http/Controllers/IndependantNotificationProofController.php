<?php

namespace Satis2020\NotificationProof\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\NotificationProofRequest;
use Satis2020\ServicePackage\Services\Auth\AuthConfigService;
use Satis2020\ServicePackage\Services\NotificationProof\NotificationProofService;

class IndependantNotificationProofController extends ApiController
{
    /**
     * @var AuthConfigService
     */
    private $authConfigService;
    protected $activityLogService;
    /**
     * @var NotificationProofService
     */
    private $notificationProofService;

    /**
     * AuthConfigController constructor.
     * @param NotificationProofService $proofService
     */
    public function __construct(NotificationProofService $proofService)
    {
        parent::__construct();
        $this->notificationProofService = $proofService;
        $this->middleware('auth:api');
        $this->middleware('permission:list-notification-proof')->only(['index']);
    }

    /**
     * @param NotificationProofRequest $request
     * @param int $pagination
     * @return Application|ResponseFactory|Response
     * @throws RetrieveDataUserNatureException
     */
    public function index(NotificationProofRequest $request,$pagination=NotificationConsts::PAGINATION_LIMIT)
    {
        return response($this->notificationProofService->filterInstitutionNotificationProofs($this->institution()->id,$request,$pagination),Response::HTTP_OK);
    }


}