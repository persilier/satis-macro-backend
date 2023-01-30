<?php

namespace Satis2020\ClaimAwaitingValidationMyInstitution\Http\Controllers\AwaitingValidation;

use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
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

        $this->middleware('permission:list-claim-awaiting-validation-my-institution')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-validation-my-institution')->only(['show']);
        $this->middleware('permission:validate-treatment-my-institution')->only(['validate', 'invalidate']);

        $this->middleware('active.pilot')->only(['index', 'show', 'validate', 'invalidate']);
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $paginationSize = \request()->query('size');
        $key = \request()->query('key');
        $type = \request()->query('type');

        return response()->json($this->getClaimsAwaitingValidationInMyInstitution(true,$paginationSize, $key, $type), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function show(Request $request,Claim $claim)
    {
        $type = isEscalationClaim($claim)?"unsatisfied":"normal";
        $claims = $this->getClaimsAwaitingValidationInMyInstitution(null,$type);

        if ($claims->search(function ($item, $key) use ($claim) {
                return $item->id == $claim->id;
            }) === false) {
            Throw new CustomException('The claim can not be showed by this pilot', 409);
        }

        return response()->json($this->showClaim($claim), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function validated(Request $request, Claim $claim)
    {

        $claim->load($this->getRelations());

        $request->merge(['claim' => $claim->id]);
        $type = isEscalationClaim($claim)?"unsatisfied":"normal";

        $rules = [
            'solution_communicated' => 'required|string',
            'mail_attachments' => 'array',
            'mail_attachments.*' => 'exists:files,id',
            'claim' => new TreatmentCanBeValidateRules($this->institution()->id,$type),
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
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function invalidated(Request $request, Claim $claim)
    {

        $claim->load($this->getRelations());
        $request->merge(['claim' => $claim->id]);

        $type = isEscalationClaim($claim)?"unsatisfied":"normal";

        $rules = [
            'claim' => new TreatmentCanBeValidateRules($this->institution()->id,$type),
            'invalidated_reason' => 'required|string'
        ];

        $this->validate($request, $rules);

        return response()->json($this->handleInvalidate($request, $claim), 201);
    }



}
