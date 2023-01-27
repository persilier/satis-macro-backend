<?php

namespace Satis2020\SatisfactionMeasuredMyClaim\Http\Controllers\ClaimSatisfactionMeasured;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;

/**
 * Class ClaimSatisfactionMeasuredController
 * @package Satis2020\SatisfactionMeasuredMyClaim\Http\Controllers\ClaimSatisfactionMeasured
 */
class ClosedClaimMeasuredController extends ApiController
{
    use ClaimSatisfactionMeasured;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-closed-my-claims')->only(['index']);
        $this->middleware('permission:close-my-claims')->only(['store']);

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
        $claims = $this->getAllMyClaim(Claim::CLAIM_CLOSED);
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
     * @param $claimId
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     * @throws CustomException
     */
    public function update(Request $request,Claim $claim)
    {

        $this->validate($request, [
            "closed_reason"=>['required','string'],
        ]);


        $claim = $claim->load('activeTreatment');

        $claim->activeTreatment->update([
            'closed_by' => $this->staff()->id,
            'closed_at' => Carbon::now(),
            'closed_reason'=>$request->closed_reason
        ]);

        $claim->update(['status'=>Claim::CLAIM_CLOSED,"closed_at"=>now()]);

        $this->activityLogService->store("Plainte clôturée",
            $this->institution()->id,
            $this->activityLogService::MEASURE_SATISFACTION,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim, 200);
    }
}


