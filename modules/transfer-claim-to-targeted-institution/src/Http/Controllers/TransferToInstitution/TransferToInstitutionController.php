<?php

namespace Satis2020\TransferClaimToTargetedInstitution\Http\Controllers\TransferToInstitution;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;
use Satis2020\ServicePackage\Notifications\TransferredToTargetedInstitution;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\AwaitingAssignment;
use Satis2020\ServicePackage\Traits\Notification;

class TransferToInstitutionController extends ApiController
{

    use AwaitingAssignment, Notification;

    private $activityLogService;

    public function __construct( ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:transfer-claim-to-targeted-institution')->only(['update']);

        $this->middleware('active.pilot')->only(['update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, Claim $claim)
    {
        $claim->load('activeTreatment');
        $activeTreatment = $claim->activeTreatment;
        if (is_null($activeTreatment)) {
            $activeTreatment = Treatment::create(['claim_id' => $claim->id]);
        }
        $activeTreatment->update([
            'transferred_to_targeted_institution_at' => Carbon::now(),
            'transferred_to_targeted_institution_by'=>$this->staff()->id
            ]);
        $claim->update(['status' => 'transferred_to_targeted_institution']);

        // send notification to pilot
        if(!is_null($this->getInstitutionPilot(Institution::find($claim->institution_targeted_id)))){
            $this->getInstitutionPilot(Institution::find($claim->institution_targeted_id))->notify(new TransferredToTargetedInstitution($claim));
        }

        $this->activityLogService->store("Plainte transférée à une institution",
            $this->institution()->id,
            ActivityLogService::TRANSFER_TO_INSTITUTION,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim, 201);
    }

}
