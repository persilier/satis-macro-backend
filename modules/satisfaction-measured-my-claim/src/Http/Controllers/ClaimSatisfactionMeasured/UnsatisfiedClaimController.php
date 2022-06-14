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
class UnsatisfiedClaimController extends ApiController
{
    use ClaimSatisfactionMeasured;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-my-claim-unsatisfied')->only(['index']);
        $this->middleware('active.pilot')->only(['index']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $key = $request->key;
        $size = $request->size;
        $type = $request->type;
        $claims = $this->getAllMyUnsatisfiedClaim($size,$key,$type);
        return response()->json($claims, 200);
    }

}


