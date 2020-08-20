<?php

namespace Satis2020\SatisfactionMeasuredAnyClaim\Http\Controllers\ClaimSatisfactionMeasured;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;


/**
 * Class ClaimSatisfactionMeasuredController
 * @package Satis2020\SatisfactionMeasuredAnyClaim\Http\Controllers\ClaimSatisfactionMeasured
 */
class ClaimSatisfactionMeasuredController extends ApiController
{
    use ClaimSatisfactionMeasured;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-satisfaction-measured-any-claim')->only(['index']);
        $this->middleware('permission:update-satisfaction-measured-any-claim')->only(['show', 'satisfactionMeasured']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        $claims = $this->getClaim()->get();
        return response()->json($claims, 200);
    }


    /**
     * @param $claim
     * @return JsonResponse
     */
    public function show($claim)
    {
        $claim = $this->getClaim()->findOrFail($claim);
        return response()->json($claim, 200);
    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws ValidationException
     */
    public function satisfactionMeasured(Request $request, $claim)
    {

        $institution = $this->institution();

        $this->validate($request, $this->rules());

        $claim = $this->getClaim()->findOrFail($claim);

        $claim->activeTreatment->update([
            'is_claimer_satisfied' => $request->is_claimer_satisfied,
            'unsatisfaction_reason' => $request->unsatisfaction_reason,
            'satisfaction_measured_by' => $this->staff()->id,
            'satisfaction_measured_at' => Carbon::now()
        ]);

        $claim->update(['status' => 'archived']);

        return response()->json($claim, 200);
    }
}


