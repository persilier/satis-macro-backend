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
class ClaimAwaitingTreatmentToAdhocController extends ApiController
{
    use ClaimAwaitingTreatment, SeveralTreatment;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:show-claim-awaiting-treatment')->only(['show']);
        $this->middleware('permission:rejected-claim-awaiting-treatment')->only(['show', 'rejectedClaim']);
        $this->middleware('permission:self-assignment-claim-awaiting-treatment')->only(['show', 'selfAssignment']);
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
        $statusColumn = "escalation_status";

        $paginationSize = \request()->query('size', 10);
        $key = \request()->query('key');

        $claims = Claim::with($this->getRelationsAwitingTreatment())
            ->where('escalation_status', Claim::CLAIM_TRANSFERRED_TO_COMITY)
            ->orWhere('escalation_status', Claim::CLAIM_AT_DISCUSSION)
            ->whereNull('deleted_at')
            ->whereNotNull('treatment_board_id')
            ->whereHas('treatmentBoard', function ($q) use ($staff) {
                $q->whereHas('members', function ($query) use ($staff) {
                    $query->where('id', $staff->id);
                });
            });

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
}
