<?php

namespace Satis2020\MyClaimIncomingByEmail\Http\Controllers\Configurations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;
use Satis2020\ServicePackage\Traits\ClaimIncomingByEmail;
use Satis2020\ServicePackage\Traits\TestSmtpConfiguration;

class ConfigurationsController extends ApiController
{
    use ClaimIncomingByEmail, TestSmtpConfiguration;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:my-email-claim-configuration')->only(['store', 'edit']);
    }


    /***
     * @param Request $request
     * @param EmailClaimConfiguration|null $emailClaimConfiguration
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request, EmailClaimConfiguration $emailClaimConfiguration = null)
    {
        
        $institution = $this->institution();

        
        $request->merge(['institution_id' => $institution->id]);
        
        $this->validate($request, $this->rulesIncomingEmail($emailClaimConfiguration ? $emailClaimConfiguration->id : null, $request->type));
        
        $configuration = $this->storeConfiguration($request, $emailClaimConfiguration, "my.register-email-claim");

        

        if ($configuration['error']) {
            return response($configuration, 400);
        }

        return response()->json($configuration['data'], 201);
    }


    public function edit()
    {
        return response()->json($this->editConfiguration($this->institution()->id), 200);
    }

}
