<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAwaitingTreatments;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
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
 * Class ClaimAwaitingTreatmentController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAwaitingTreatments
 */
class ClaimAwaitingTreatmentController extends ApiController
{
    use ClaimAwaitingTreatment, SeveralTreatment;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        // $this->middleware('permission:list-claim-awaiting-treatment')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-treatment')->only(['show']);
        $this->middleware('permission:rejected-claim-awaiting-treatment')->only(['show', 'rejectedClaim']);
        $this->middleware('permission:self-assignment-claim-awaiting-treatment')->only(['show', 'selfAssignment']);
        //$this->middleware('permission:assignment-claim-awaiting-treatment')->only(['edit', 'assignmentClaimStaff']);
        //$this->middleware('permission:unfounded-claim-awaiting-treatment')->only(['unfoundedClaim']);
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index(Request $request)
    {
        $type = $request->query('type', "normal");

        $institution = $this->institution();
        $staff = $this->staff();
        $statusColumn = $type == Claim::CLAIM_UNSATISFIED ? "escalation_status" : "status";

        $paginationSize = \request()->query('size');
        $key = \request()->query('key');

        $claims = $this->getClaimsQuery($institution->id, $staff->unit_id, $statusColumn);

        if ($key) {
            switch ($type) {
                case 'reference':
                    $claims = $claims->where('reference', 'LIKE', "%$key%");
                    break;
                case 'claimObject':
                    $claims = $claims->whereHas("claimObject", function ($query) use ($key) {
                        $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    });
                    break;
                default:
                    $claims = $claims->whereHas("claimer", function ($query) use ($key) {
                        $query->where('firstname', 'like', "%$key%")
                            ->orWhere('lastname', 'like', "%$key%")
                            ->orWhere('raison_sociale', 'like', "%$key%")
                            ->orwhereJsonContains('telephone', $key)
                            ->orwhereJsonContains('email', $key);
                    });
                    break;
            }
        }


        return response()->json($claims->paginate($paginationSize), 200);
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

        if (!$this->canRejectClaim($claim)) {
            return $this->errorResponse('Can not reject this claim', 403);
        }

        $claim = $this->rejectedClaimUpdate($claim, $request);

        $this->activityLogService->store(
            "Une réclamation a été rejetée",
            $this->institution()->id,
            $this->activityLogService::REJECTED_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

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

        $this->activityLogService->store(
            "Une réclamation a été s'est auto affecté à un staff",
            $this->institution()->id,
            $this->activityLogService::AUTO_ASSIGNMENT_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

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

        $this->activityLogService->store(
            "Une réclamation a été affecté à un staff",
            $this->institution()->id,
            $this->activityLogService::ASSIGNMENT_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim, 200);
    }
}
