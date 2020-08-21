<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAwaitingTreatments;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;

/**
 * Class ClaimAwaitingTreatmentController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAwaitingTreatments
 */
class ClaimAwaitingTreatmentController extends ApiController
{
    use ClaimAwaitingTreatment;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-awaiting-treatment')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-treatment')->only(['show']);
        $this->middleware('permission:rejected-claim-awaiting-treatment')->only(['show', 'rejectedClaim']);
        $this->middleware('permission:self-assignment-claim-awaiting-treatment')->only(['show', 'selfAssignment']);
        //$this->middleware('permission:assignment-claim-awaiting-treatment')->only(['edit', 'assignmentClaimStaff']);
        //$this->middleware('permission:unfounded-claim-awaiting-treatment')->only(['unfoundedClaim']);
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
        $staff = $this->staff();

        $claims = $this->getClaimsQuery($institution->id, $staff->unit_id)->get()->map(function ($item, $key) {
            $item = Claim::with($this->getRelationsAwitingTreatment())->find($item->id);
            return $item;
        });
        return response()->json($claims, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function show($claim)
    {
        $staff = $this->staff();

        $claim = $this->getOneClaimQuery($staff->unit_id, $claim);
        return response()->json(Claim::with($this->getRelationsAwitingTreatment())->findOrFail($claim->id), 200);
    }


    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function edit($claim)
    {
        $staff = $this->staff();

        $claim = $this->getOneClaimQuery($staff->unit_id, $claim);

        return response()->json([
            'claim' => $claim,
            'staffs' => $this->getTargetedStaffFromUnit($staff->unit_id)
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function showClaimQueryTreat($claim)
    {
        $institution = $this->institution();
        $staff = $this->staff();

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);
        return response()->json($claim, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Claim $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    public function rejectedClaim(Request $request, $claim)
    {
        $staff = $this->staff();

        $this->validate($request, $this->rules($staff, 'rejected'));

        $claim = $this->getOneClaimQuery($staff->unit_id, $claim);

        $claim = $this->rejectedClaimUpdate($claim, $request);

        return response()->json($claim, 200);

    }


    /**
     * @param $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     */
    protected function selfAssignmentClaim($claim)
    {

        $staff = $this->staff();

        $claim = $this->getOneClaimQuery($staff->unit_id, $claim);

        $claim = $this->assignmentClaim($claim, $staff->id);

        return response()->json($claim, 200);
    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    protected function assignmentClaimStaff(Request $request, $claim)
    {

        $staff = $this->staff();

        if (!$this->checkLead($staff)) {
            throw new CustomException("Impossible d'affecter cette réclamation à un staff.");
        }

        $this->validate($request, $this->rules($staff));

        $claim = $this->getOneClaimQuery($staff->unit_id, $claim);

        $claim = $this->assignmentClaim($claim, $request->staff_id);

        Staff::with('identite')->find($request->staff_id)->identite->notify(new \Satis2020\ServicePackage\Notifications\AssignedToStaff($claim));

        return response()->json($claim, 200);
    }


}
