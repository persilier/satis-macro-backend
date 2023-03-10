<?php

namespace Satis2020\ReviveStaff\Http\Controllers\ReviveStaff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Notifications\ReviveStaff;
use Satis2020\ServicePackage\Services\RevivalService;
use Satis2020\ServicePackage\Services\StaffService;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Traits\UnitTrait;
use Symfony\Component\HttpFoundation\Response;

class ReviveStaffController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\Notification, UnitTrait;

    /**
     * @var StaffService
     */
    private $staffService;
    /**
     * @var RevivalService
     */
    private $revivalService;

    public function __construct(RevivalService $revivalService, StaffService $staffService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-unit-revivals')->only(['index']);
        $this->middleware('permission:revive-staff')->only(['store']);

        $this->staffService = $staffService;
        $this->revivalService = $revivalService;
    }

    public function index(Request $request)
    {
        if (!$this->staffIsUnitLead($this->staff())) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $size = $request->get("size");

        return $this->revivalService->getUnitRevivals($this->staff()->unit_id, $size);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Claim $claim)
    {
        if (!$this->checkIfStaffIsPilot($this->staff()) && !$this->staffIsUnitLead($this->staff()) && !($this->staff()->identite->user->hasRole('collector-filial-pro') && $claim->status == Claim::CLAIM_FULL)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'text' => 'required',
        ];

        $this->validate($request, $rules);

        $staffs = $this->staffService->getStaffsByIdentity(collect($this->getStaffToReviveIdentities($claim))->pluck("id")->toArray());
        $this->revivalService->storeRevival($staffs, $request->text, $claim);
        Notification::send($this->getStaffToReviveIdentities($claim), new ReviveStaff($claim, $request->text));

        return response()->json($claim, Response::HTTP_OK);
    }
}
