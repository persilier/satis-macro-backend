<?php

namespace Satis2020\SatisfactionMeasuredMyClaim\Http\Controllers\ClaimSatisfactionMeasured;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;

/**
 * Class ClaimSatisfactionMeasuredController
 * @package Satis2020\SatisfactionMeasuredMyClaim\Http\Controllers\ClaimSatisfactionMeasured
 */
class ClaimSatisfactionMeasuredController extends ApiController
{
    use ClaimSatisfactionMeasured;

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
    public function index()
    {
        $claims = $this->getAllMyClaim();
        return response()->json($claims, 200);
    }


    /**
     * @param $claim
     * @return JsonResponse
     */
    public function show($claim)
    {
        $claim = $this->getOneMyClaim($claim);
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
    public function satisfactionMeasured(Request $request, $claim)
    {

        if ($request->isNotFilled("note")){
            $request->request->remove("note");
        }
        $this->validate($request, $this->rules($request));

        $claim = $this->getOneMyClaim($claim);

        $claim->activeTreatment->update([
            'is_claimer_satisfied' => $request->is_claimer_satisfied,
            'unsatisfied_reason' => $request->unsatisfaction_reason,
            'satisfaction_measured_by' => $this->staff()->id,
            'satisfaction_measured_at' => Carbon::now(),
            'note' => $request->note
        ]);

        $claim->update(['status' => 'archived', 'archived_at' => Carbon::now()]);

        $this->activityLogService->store("Mesure de satisfaction",
            $this->institution()->id,
            $this->activityLogService::MEASURE_SATISFACTION,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim, 200);
    }
}


