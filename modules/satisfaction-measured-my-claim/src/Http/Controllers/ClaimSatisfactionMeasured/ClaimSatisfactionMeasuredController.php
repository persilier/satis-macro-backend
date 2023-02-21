<?php

namespace Satis2020\SatisfactionMeasuredMyClaim\Http\Controllers\ClaimSatisfactionMeasured;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Satis2020\Webhooks\Consts\Event;
use Satis2020\Webhooks\Facades\SendEvent;
use Satis2020\ServicePackage\Models\Claim;
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
class ClaimSatisfactionMeasuredController extends ApiController
{
    use ClaimSatisfactionMeasured, SeveralSatisfactionMesured;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-satisfaction-measured-my-claim')->only(['index']);
        $this->middleware('permission:update-satisfaction-measured-my-claim')->only(['show', 'satisfactionMeasured']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index(Request $request)
    {
        $paginationSize = \request()->query('size');
        $key = \request()->query('key');
        $statusColumn = $request->query('type', "normal") == Claim::CLAIM_UNSATISFIED ? "escalation_status" : "status";
        
        $claims = $this->getAllMyClaim(Claim::CLAIM_VALIDATED, true, $paginationSize, $key, $statusColumn);
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
        $claim = $this->getOneMyClaim($claim->id, Claim::CLAIM_VALIDATED, $statusColumn);
        return response()->json($claim, 200);
    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function satisfactionMeasured(Request $request, Claim $claim)
    {

        if ($request->isNotFilled("note")) {
            $request->request->remove("note");
        }
        $this->validate($request, $this->rules($request));

        $statusColumn = isEscalationClaim($claim) ? "escalation_status" : "status";
        $claim = $this->getOneMyClaim($claim->id, Claim::CLAIM_TRANSFERRED_TO_STAFF_FOR_SATISFACTION, $statusColumn);
        $backUp = null;
        if (!is_null($claim->activeTreatment->is_claimer_satisfied) && is_null($claim->activeTreatment->satisfaction_history)) {
            $backUp = $this->backupData($claim);
        }
        
        $claim->activeTreatment->update([
            'is_claimer_satisfied' => $request->is_claimer_satisfied,
            'unsatisfied_reason' => $request->unsatisfaction_reason,
            'satisfaction_measured_by' => $this->staff()->id,
            'satisfaction_measured_at' => Carbon::now(),
            'note' => $request->note,
            'satisfaction_history' => $backUp
        ]);
            // If treatments is null, initialize it at empty array
        if ($request->is_claimer_satisfied) {
            $claim->update([$statusColumn => Claim::CLAIM_ARCHIVED, 'archived_at' => Carbon::now()]);
        } else {
            $claim->update([$statusColumn => Claim::CLAIM_UNSATISFIED]);
        }

        $this->activityLogService->store(
            "Mesure de satisfaction",
            $this->institution()->id,
            $this->activityLogService::MEASURE_SATISFACTION,
            'claim',
            $this->user(),
            $claim
        );
        $backUp = $this->backupData($claim);
        $claim->activeTreatment->update([
            'satisfaction_history' => $backUp
        ]);
        $claim->refresh();
        //sending webhook event
        SendEvent::sendEvent(Event::SATISFACTION_MEASURED, $claim->toArray(), $claim->institution_targeted_id);

        return response()->json($claim, 200);
    }
}
