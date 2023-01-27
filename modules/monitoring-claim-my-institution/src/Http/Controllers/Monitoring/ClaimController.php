<?php

namespace Satis2020\MonitoringClaimMyInstitution\Http\Controllers\Monitoring;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\MonitoringClaim;

/**
 * Class ClaimController
 * @package Satis2020\MonitoringClaimMyInstitution\Http\Controllers\Monitoring
 */
class ClaimController extends ApiController
{
    use MonitoringClaim, DataUserNature, ConfigurationPilotTrait;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-monitoring-claim-my-institution')->only(['index', 'show']);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $institution = $this->institution();
        $request->merge(['institution_id' => $institution->id]);

        $this->validate($request, $this->rules($request));
        $configuration  = $this->nowConfiguration()['configuration'];
        $lead_pilot  = $this->nowConfiguration()['lead_pilot'];
        $all_active_pilots  = $this->nowConfiguration()['all_active_pilots'];

        if ($configuration->many_active_pilot  === "1" && $this->staff()->id != $lead_pilot->id) {
            $incompletes = $this->getAllClaim($request, 'incomplete');

            $toAssignedToUnit = $this->getAllClaim($request, 'full');

            $toAssignedToUStaff = $this->getAllClaim($request, 'transferred_to_unit', true);

            $awaitingTreatment = $this->getAllClaim($request, 'assigned_to_staff', true);

            $toValidate = $this->getAllClaim($request, 'treated', true);

            $toMeasureSatisfaction = $this->getAllClaim($request, 'validated', true); 

        } else {
            $incompletes = $this->getAllClaim($request, 'incomplete');

            $toAssignedToUnit = $this->getAllClaim($request, 'full');

            $toAssignedToUStaff = $this->getAllClaim($request, 'transferred_to_unit', true);

            $awaitingTreatment = $this->getAllClaim($request, 'assigned_to_staff', true);

            $toValidate = $this->getAllClaim($request, 'treated', true);

            $toMeasureSatisfaction = $this->getAllClaim($request, 'validated', true);

        }
        $claims =  $this->metaData($incompletes, $toAssignedToUnit, $toAssignedToUStaff, $awaitingTreatment, $toValidate, $toMeasureSatisfaction, $institution->id);

        return response()->json($claims, 200);
    }


    /**
     * @param $claim
     * @return JsonResponse
     */
    public function show($claim)
    {

        $claim = $this->getOne($claim, $this->institution()->id);
        return response()->json($claim, 200);
    }
}
