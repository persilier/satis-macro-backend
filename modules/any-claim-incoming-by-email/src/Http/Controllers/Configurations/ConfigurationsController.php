<?php

namespace Satis2020\AnyClaimIncomingByEmail\Http\Controllers\Configurations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClaimIncomingByEmail;
use Satis2020\ServicePackage\Traits\TestSmtpConfiguration;

class ConfigurationsController extends ApiController
{
    use ClaimIncomingByEmail, TestSmtpConfiguration;
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:any-email-claim-configuration')->only(['store', 'edit']);

        $this->activityLogService = $activityLogService;
    }

    /***
     * @param Request $request
     * @param EmailClaimConfiguration|null $emailClaimConfiguration
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request, EmailClaimConfiguration $emailClaimConfiguration = null)
    {
        $this->validate($request, $this->rulesIncomingEmail($emailClaimConfiguration ? $emailClaimConfiguration->id : null));

        $configuration = $this->storeConfiguration($request, $emailClaimConfiguration, "any.register-email-claim");

        if ($configuration['error']) {
            return $this->errorResponse($configuration, 400);
        }

        $this->activityLogService->store("Enregistrement ou mise à jour de la configuration de 
            email pour la réception des réclamation par mail.",
            $this->institution()->id,
            $this->activityLogService::CREATED,
            'claim_incoming_email',
            $this->user(),
            $configuration
        );

        return response()->json($configuration['data'], 201);
    }


    public function edit()
    {
        return response()->json(Institution::with('emailClaimConfiguration')->get(), 200);
    }

}
