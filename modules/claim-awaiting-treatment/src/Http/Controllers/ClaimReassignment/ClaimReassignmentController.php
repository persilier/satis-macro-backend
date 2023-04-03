<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimReassignment;

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
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Traits\SeveralTreatment;

/**
 * Class ClaimReassignmentController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimReassignment
 */
class ClaimReassignmentController extends ApiController
{
    use ClaimAwaitingTreatment, SeveralTreatment;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:assignment-claim-awaiting-treatment')->except(['store', 'destroy']);
        $this->activityLogService = $activityLogService;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    protected function index(Request $request)
    {

        $type = $request->query('type', 'normal');
        $this->checkLeadReassignment();

        return response()->json($this->queryClaimReassignment($type)->get(), 200);
    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws CustomException
     */
    protected function show(Request $request, $claim)
    {
        $type = $request->query('type', 'normal');

        $this->checkLeadReassignment();
        return response()->json($this->queryClaimReassignment($type)->find($claim), 200);
    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     */
    protected function edit(Request $request, $claim)
    {

        $type = $request->query('type', 'normal');

        $staff = $this->staff();
        $this->checkLeadReassignment();
        $claimToReassign = $this->queryClaimReassignment($type)->find($claim);
        $staffs = $claimToReassign->activeTreatment->responsible_staff_id != null ?
            $this->getTargetedStaffFromUnitForReassignment($staff->unit_id, $claimToReassign->activeTreatment->responsible_staff_id) : $this->getTargetedStaffFromUnit($staff->unit_id);
        return response()->json([
            'claim' => $claimToReassign,
            'staffs' => $staffs,
        ], 200);
    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    protected function update(Request $request, $claim)
    {

        $type = $request->query('type', 'normal');

        $this->checkLeadReassignment();
        $claim = $this->queryClaimReassignment($type)->find($claim);
        $this->validate($request, $this->rules($this->staff()));
        $claim->activeTreatment->update([
            'responsible_staff_id' => $request->staff_id,
        ]);

        $this->activityLogService->store(
            "Une réclamation a été réaffecté à un autre staff",
            $this->institution()->id,
            $this->activityLogService::REASSIGNMENT_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim, 201);
    }
}
