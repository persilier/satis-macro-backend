<?php

namespace Satis2020\AuthConfig\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\AuthConfigRequest;
use Satis2020\ServicePackage\Services\Auth\AuthConfigService;

class AuthConfigController extends ApiController
{
    /**
     * @var AuthConfigService
     */
    private $authConfigService;

    /**
     * AuthConfigController constructor.
     * @param AuthConfigService $authConfigService
     */
    public function __construct(AuthConfigService $authConfigService)
    {
        parent::__construct();
        $this->authConfigService = $authConfigService;
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
     */
    public function update(AuthConfigRequest $request)
    {
        return response($this->authConfigService->updateConfig($request),Response::HTTP_OK);
    }
}