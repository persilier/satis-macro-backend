<?php

namespace Satis2020\AnyClaimIncomingByEmail\Http\Controllers\Configurations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ClaimIncomingByEmail;
use Satis2020\ServicePackage\Traits\TestSmtpConfiguration;

class ConfigurationsController extends ApiController
{
    use ClaimIncomingByEmail, TestSmtpConfiguration;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:any-email-claim-configuration')->only(['store', 'edit']);
    }


    public function store(Request $request, EmailClaimConfiguration $emailClaimConfiguration = null)
    {
        $this->validate($request, $this->rulesIncomingEmail($emailClaimConfiguration ? $emailClaimConfiguration->id : null));

        $configuration = $this->storeConfiguration($request, $emailClaimConfiguration, "any.register-email-claim");

        if ($configuration['error']) {
            return $this->errorResponse($configuration['message'], 400);
        }

        return response()->json($configuration['data'], 201);
    }


    public function edit()
    {
        return response()->json(Institution::with('emailClaimConfiguration')->get(), 200);
    }

}
