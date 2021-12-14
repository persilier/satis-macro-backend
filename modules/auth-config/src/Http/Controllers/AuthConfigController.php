<?php

namespace Satis2020\AuthConfigPackage\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\AuthConfigPackage\Services\AuthConfigService;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\AuthConfigRequest;

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