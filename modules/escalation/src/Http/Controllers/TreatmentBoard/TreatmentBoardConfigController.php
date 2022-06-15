<?php

namespace Satis2020\Escalation\Http\Controllers\TreatmentBoard;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Requests\EscalationConfigRequest;
use Satis2020\Escalation\Requests\TreatmentBoardRequest;
use Satis2020\Escalation\Services\EscalationConfigService;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Services\TreatmentBoardService;

class TreatmentBoardConfigController extends ApiController
{
    /**
     * @var TreatmentBoardService
     */
    private $treatmentBordService;
    protected $activityLogService;

    /**
     * EscalationConfigController constructor.
     * @param TreatmentBoardService $treatmentBordService
     * @param ActivityLogService $activityLogService
     */
    public function __construct(TreatmentBoardService $treatmentBordService, ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->treatmentBordService = $treatmentBordService;
        $this->activityLogService = $activityLogService;
        $this->middleware('auth:api');
        $this->middleware('permission:list-treatment-board')->only(['index']);
        $this->middleware('permission:store-list-treatment-board')->only(['store']);
        $this->middleware('permission:update-list-treatment-board')->only(['update']);
    }



    /**
     * @param EscalationConfigRequest $request
     * @return Application|ResponseFactory|Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(TreatmentBoardRequest $request,TreatmentBoard $treatmentBoard)
    {
        $configAuth = $this->treatmentBordService->update($request,$treatmentBoard);

        $this->activityLogService->store("Mise à jour des commités",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'config_auth',
            $this->user(),
            $configAuth
        );
        return response($configAuth,Response::HTTP_OK);
    }
}