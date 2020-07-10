<?php

namespace Satis2020\ClaimSatisfactionMeasured\Http\Controllers\ClaimSatisfactionMeasured;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;

/**
 * Class ClaimAwaitingTreatmentController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAwaitingTreatments
 */
class ClaimSatisfactionMeasuredController extends ApiController
{
    use ClaimSatisfactionMeasured;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-satisfaction-measured')->only(['index']);
        $this->middleware('permission:update-claim-satisfaction-measured')->only(['show', 'satisfactionMeasured']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        $institution = $this->institution();
        $claims = $this->getClaim($institution->id)->get();
        return response()->json($claims, 200);
    }


    /**
     * @param $claim
     * @return JsonResponse
     */
    public function show($claim)
    {
        $institution = $this->institution();
        $claim = $this->getClaim($institution->id)->findOrFail($claim);
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

        $claim = $this->getClaim($institution->id)->findOrFail($claim);

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


