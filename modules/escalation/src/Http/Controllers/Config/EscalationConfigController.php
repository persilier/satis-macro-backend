<?php

namespace Satis2020\Escalation\Http\Controllers\Config;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\Escalation\Requests\EscalationConfigRequest;
use Satis2020\Escalation\Services\EscalationConfigService;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class EscalationConfigController extends ApiController
{
    /**
     * @var EscalationConfigService
     */
    private $escalationConfigService;
    protected $activityLogService;

    /**
     * EscalationConfigController constructor.
     * @param EscalationConfigService $escalationConfigService
     * @param ActivityLogService $activityLogService
     */
    public function __construct(EscalationConfigService $escalationConfigService, ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->escalationConfigService = $escalationConfigService;
        $this->activityLogService = $activityLogService;
        $this->middleware('auth:api');
        //$this->middleware('permission:list-escalation-config')->only(['show']);
        $this->middleware('permission:update-escalation-config')->only(['update']);
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function show()
    {
        return response($this->escalationConfigService->get(),Response::HTTP_OK);
    }

    /**
     * @param EscalationConfigRequest $request
     * @return Application|ResponseFactory|Response
     * @throws RetrieveDataUserNatureException
     */
    public function update(EscalationConfigRequest $request)
    {
        $request->merge(['institution_id'=>$this->institution()->id]);
        $config = $this->escalationConfigService->updateConfig($request);

        $this->activityLogService->store("Mise à jour des configurations d'escalade",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'config_auth',
            $this->user(),
            $config
        );
        return response($config,Response::HTTP_OK);
    }
}