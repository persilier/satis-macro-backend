<?php

namespace Satis2020\AuthConfig\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\AuthConfigRequest;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Services\Auth\AuthConfigService;

class AuthConfigController extends ApiController
{
    /**
     * @var AuthConfigService
     */
    private $authConfigService;
    protected $activityLogService;

    /**
     * AuthConfigController constructor.
     * @param AuthConfigService $authConfigService
     * @param ActivityLogService $activityLogService
     */
    public function __construct(AuthConfigService $authConfigService, ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->authConfigService = $authConfigService;
        $this->activityLogService = $activityLogService;
        $this->middleware('auth:api');
        $this->middleware('permission:list-auth-config')->only(['show']);
        $this->middleware('permission:update-auth-config')->only(['update']);
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function show()
    {
        return response($this->authConfigService->get(),Response::HTTP_OK);
    }

    /**
     * @param AuthConfigRequest $request
     * @return Application|ResponseFactory|Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(AuthConfigRequest $request)
    {
        $configAuth = $this->authConfigService->updateConfig($request);

        $this->activityLogService->store("Mise à jour des configurations d'authentification",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'config_auth',
            $this->user(),
            $configAuth
        );
        return response($configAuth,Response::HTTP_OK);
    }
}