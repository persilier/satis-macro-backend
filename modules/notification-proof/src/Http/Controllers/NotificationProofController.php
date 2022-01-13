<?php

namespace Satis2020\NotificationProof\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Repositories\InstitutionRepository;
use Satis2020\ServicePackage\Requests\NotificationProofRequest;
use Satis2020\ServicePackage\Services\ActivityLog\NotificationProofService;
use Satis2020\ServicePackage\Services\Auth\AuthConfigService;

class NotificationProofController extends ApiController
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
        $this->middleware('permission:list-any-notification-proof')->only(['index']);
        $this->middleware('permission:list-any-notification-proof')->only(['create']);
    }

    /**
     * @param NotificationProofRequest $request
     * @param int $pagination
     * @return Application|ResponseFactory|Response
     */
    public function index(NotificationProofRequest $request,$pagination=NotificationConsts::PAGINATION_LIMIT)
    {
        return response($this->notificationProofService->filterNotificationProofs($request,$pagination),Response::HTTP_OK);
    }

    /**
     *
     */
    public function create()
    {
        $institutionRepository = app(InstitutionRepository::class);
        return \response($institutionRepository->getAll(),Response::HTTP_OK);
    }


}