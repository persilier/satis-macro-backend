<?php

namespace Satis2020\ClaimAwaitingValidationAnyInstitution\Http\Controllers\AwaitingValidation;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Rules\TreatmentCanBeValidateRules;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\AwaitingValidation;
use Satis2020\ServicePackage\Traits\SeveralTreatment;

class AwaitingValidationController extends ApiController
{

    use AwaitingValidation, SeveralTreatment;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

     //   $this->middleware('permission:list-claim-awaiting-validation-any-institution')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-validation-any-institution')->only(['show']);
        $this->middleware('permission:validate-treatment-any-institution')->only(['validate', 'invalidate']);

        $this->middleware('active.pilot')->only(['index', 'show', 'validate', 'invalidate']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index()
    {
        $claimsTreated = Claim::with($this->getRelations())->where('status', 'treated')->get();
        return response()->json($claimsTreated->map(function ($item, $key) {
            $item->activeTreatment->load($this->getActiveTreatmentRelations());
            return $item;
        }), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Claim $claim)
    {
        return response()->json($this->showClaim($claim), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function validated(Request $request, Claim $claim)
    {
        $claim->load($this->getRelations());

        $rules = [
            'solution_communicated' => 'required|string'
        ];

        $this->validate($request, $rules);

        return response()->json($this->handleValidate($request, $claim), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function invalidated(Request $request, Claim $claim)
    {
        $claim->load($this->getRelations());

        $rules = [
            'invalidated_reason' => 'required|string'
        ];

        $this->validate($request, $rules);

        return response()->json($this->handleInvalidate($request, $claim), 201);
    }
}
