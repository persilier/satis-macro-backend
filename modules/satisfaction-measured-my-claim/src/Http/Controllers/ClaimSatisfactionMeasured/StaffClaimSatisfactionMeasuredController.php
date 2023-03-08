<?php

namespace Satis2020\SatisfactionMeasuredMyClaim\Http\Controllers\ClaimSatisfactionMeasured;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Satis2020\Webhooks\Consts\Event;
use Satis2020\Webhooks\Facades\SendEvent;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;
use Satis2020\ServicePackage\Traits\SeveralSatisfactionMesured;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;

/**
 * Class ClaimSatisfactionMeasuredController
 * @package Satis2020\SatisfactionMeasuredMyClaim\Http\Controllers\ClaimSatisfactionMeasured
 */
class StaffClaimSatisfactionMeasuredController extends ApiController
{
    use ClaimSatisfactionMeasured, SeveralSatisfactionMesured;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:staff-list-satisfaction-measured-my-claim')->only(['index']);
        $this->middleware(['permission:affect-claim-for-satisfaction'])->only(['affectForSatisfactionMeasure']);
        $this->middleware(['permission:auto-affect-claim-for-satisfaction-collector'])->only(['autoAffectForSatisfactionMeasure']);

        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $paginationSize = \request()->query('size');
        $key = \request()->query('key');
        $statusColumn = $request->query('type', "normal") == Claim::CLAIM_UNSATISFIED ? "escalation_status" : "status";

        $claims = $this->getAllForSatisfactionMyClaim(Claim::CLAIM_TRANSFERRED_TO_STAFF_FOR_SATISFACTION, true, $paginationSize, $key, $statusColumn);
        return response()->json($claims, 200);
    }

    /**
     * @param $claim
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function show(Claim $claim)
    {
        $statusColumn = isEscalationClaim($claim) ? "escalation_status" : "status";
        $claim = $this->getOneMyClaim($claim->id, Claim::CLAIM_TRANSFERRED_TO_STAFF_FOR_SATISFACTION, $statusColumn);
        return response()->json($claim, 200);
    }


    public function create()
    {
        // get all pilot , all collectors and all staff those  are the posibitity to do the satisfaction mesure
        return response(Staff::with('identite.user.roles')->whereHas('identite.user.roles', function ($query) {
            $query->whereIn('name', ['pilot', 'collector-filial-pro', 'satisfaction-mesure']);
        })->get(), 200);
    }
    public function autoAffectForSatisfactionMeasure(Request $request)
    {

        // rules
        $rules = [
            'claim' => ['required', 'exists:claims,id'],
            'staff' => ['required', 'exists:staff,id'],
        ];

        // valide
        $request->validate($rules);

        // Get claim and staff

        $claim = Claim::find($request->claim);
        $statusColumn = isEscalationClaim($claim) ? "escalation_status" : "status";
        $claim = $this->getOneMyClaim($claim->id, Claim::CLAIM_VALIDATED, $statusColumn);

        $staff = Staff::with('unit')->find($request->staff);


        // Check if auto affecation or not
        $claim->update([
            "$statusColumn" => Claim::CLAIM_TRANSFERRED_TO_STAFF_FOR_SATISFACTION
        ]);

        $data = [
            'satisfaction_responsible_staff_id' => $staff->id,
            'satisfaction_responsible_unit_id' => $staff->unit_id,
            'transfered_to_satisfaction_staff_by' => $this->staff()->id,
            'transfered_to_satisfaction_staff_by_unit' => $this->staff()->unit_id,
            'transfered_to_satisfaction_responsible_at' => now()
        ];

        // Affect
        $claim->activeTreatment->update($data);

        //Log
        $this->activityLogService->store(
            "Une réclamation a été s'est auto affecté à un staff pour mesure de satisfaction",
            $this->institution()->id,
            $this->activityLogService::AUTO_ASSIGNMENT_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        // Notifications

        // return response
        return response()->json($claim, 200);
    }

    public function affectForSatisfactionMeasure(Request $request)
    {

        // rules
        $rules = [
            'claim' => ['required', 'exists:claims,id'],
            'staff' => ['required', 'exists:staff,id'],
        ];

        // valide
        $request->validate($rules);

        // Get claim and staff

        $claim = Claim::find($request->claim);
        $statusColumn = isEscalationClaim($claim) ? "escalation_status" : "status";
        $claim = $this->getOneMyClaim($claim->id, Claim::CLAIM_VALIDATED, $statusColumn);

        $staff = Staff::with('unit')->find($request->staff);


        // Check if auto affecation or not
        $claim->update([
            "$statusColumn" => Claim::CLAIM_TRANSFERRED_TO_STAFF_FOR_SATISFACTION
        ]);

        $data = [
            'satisfaction_responsible_staff_id' => $staff->id,
            'satisfaction_responsible_unit_id' => $staff->unit_id,
            'transfered_to_satisfaction_staff_by' => $this->staff()->id,
            'transfered_to_satisfaction_staff_by_unit' => $this->staff()->unit_id,
            'transfered_to_satisfaction_responsible_at' => now()
        ];

        // Affect
        $claim->activeTreatment->update($data);

        //Log
        $this->activityLogService->store(
            "Une réclamation a été s'est auto affecté à un staff pour mesure de satisfaction",
            $this->institution()->id,
            $this->activityLogService::AUTO_ASSIGNMENT_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        // Notifications

        // return response
        return response()->json($claim, 200);
    }
}
