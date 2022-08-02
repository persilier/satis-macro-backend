<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\PilotTreatment;

use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;

/**
 * Class ClaimAssignmentToStaffController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAssignmentToStaffs
 */
class UnfoundedClaimController extends ApiController
{
    use ClaimAwaitingTreatment;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:unfounded-claim-awaiting-assignment')->only(['update']);

        $this->middleware('active.pilot')->only(['update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    protected function update(Request $request, Claim $claim)
    {

        $institution = $this->institution();
        $staff = $this->staff();

        $rules = [
            'unfounded_reason' => ['required', 'string'],
            'solution_communicated' => ['required', 'string'],
        ];

        $this->validate($request, $rules);

        $claim->load(['createdBy', 'activeTreatment']);

        // return unauthorized when the user have not the right to treat this claim
        if (!(
            ((is_null($claim->createdBy) ? $claim->institution_targeted_id : $claim->createdBy->institution_id) == $institution->id && $claim->status == 'full')
            || ($claim->institution_targeted_id == $institution->id && $claim->status == 'transferred_to_targeted_institution')
        )) {
            return $this->errorResponse("Unauthorized", 401);
        }

        $activeTreatment = $claim->activeTreatment;

        // create activeTreatment if it's null
        if (is_null($claim->activeTreatment)) {

            $activeTreatment = Treatment::create([
                'claim_id' => $claim->id,
            ]);

            $claim->update(['active_treatment_id' => $activeTreatment->id]);
        }

        // update activeTreatment
        $activeTreatment->update([
            'responsible_unit_id' => $staff->unit_id,
            'assigned_to_staff_by' => $staff->id,
            'responsible_staff_id' => $staff->id,
            'unfounded_reason' => $request->unfounded_reason,
            'transferred_to_unit_at' => Carbon::now(),
            'declared_unfounded_at' => Carbon::now(),
            'amount_returned' => NULL,
            'solution' => NULL,
            'comments' => NULL,
            'preventive_measures' => NULL,
            'assigned_to_staff_at' => Carbon::now(),
            'solved_at' => NULL,
            'solution_communicated' => $request->solution_communicated,
            'validated_at' => Carbon::now(),
            'invalidated_reason' => NULL
        ]);

        $claim->update(['status' => 'archived']);

        $claim->load(['createdBy', 'activeTreatment']);

        $this->activityLogService->store("Une réclamation a été déclarée non fondé par le pilot actif",
            $this->institution()->id,
            $this->activityLogService::UNFOUNDED_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        // send notification to claimer
        $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolutionUnfounded($claim));

        return response()->json($claim, 200);

    }


}
