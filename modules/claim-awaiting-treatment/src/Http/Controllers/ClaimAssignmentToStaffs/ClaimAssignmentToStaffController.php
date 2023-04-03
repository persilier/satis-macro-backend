<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAssignmentToStaffs;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Validation\Rules\RequiredIf;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Notifications\TreatAClaim;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;

/**
 * Class ClaimAssignmentToStaffController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAssignmentToStaffs
 */
class ClaimAssignmentToStaffController extends ApiController
{
    use ClaimAwaitingTreatment, ConfigurationPilotTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-assignment-to-staff')->only(['index']);
        $this->middleware('permission:show-claim-assignment-to-staff')->only(['show', 'treatmentClaim', 'unfoundedClaim']);

        $this->activityLogService = $activityLogService;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index(Request $request)
    {
        $type = $request->query('type', "normal");
        $page = $request->query('size', 15);
        $institution = $this->institution();
        $staff = $this->staff();
        $claims = [];
        if ($this->checkIfStaffIsPilot($staff)) {
            $claims = $this->getClaimsQuery($institution->id, $staff->unit_id)->paginate($page);
            $datas = $claims->getCollection();
            $datas = $datas->map(function ($item, $key) {
                $item->with($this->getRelationsAwitingTreatment());
            });
            $claims->setCollection($datas);
        } else {
            $claims = $this->getClaimsTreat($institution->id, $staff->unit_id, $staff->id)->paginate($page);
            $datas = $claims->getCollection();
            $datas = $datas->map(function ($item, $key) {
                $item = Claim::with($this->getRelationsAwitingTreatment())->find($item->id);
                $item->activeTreatment->load(['responsibleUnit', 'assignedToStaffBy.identite', 'responsibleStaff.identite']);
                $item->isInvalidTreatment = (!is_null($item->activeTreatment->invalidated_reason) && !is_null($item->activeTreatment->validated_at)) ? TRUE : FALSE;
                return $item;
            });
            $claims->setCollection($datas);
        }

        $statusColumn = $type == Claim::CLAIM_UNSATISFIED ? "escalation_status" : "status";

        $claims = $this->getClaimsTreat($institution->id, $staff->unit_id, $staff->id, $statusColumn)
            ->paginate($page);
        $datas = $claims->getCollection();
        $datas = $datas->map(function ($item, $key) {
            $item = Claim::with($this->getRelationsAwitingTreatment())->find($item->id);
            $item->activeTreatment->load(['responsibleUnit', 'assignedToStaffBy.identite', 'responsibleStaff.identite']);
            $item->isInvalidTreatment = (!is_null($item->activeTreatment->invalidated_reason) && !is_null($item->activeTreatment->validated_at)) ? TRUE : FALSE;
            return $item;
        });
        $claims->setCollection($datas);
        return response()->json($claims, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function show($claim)
    {
        $institution = $this->institution();
        $staff = $this->staff();


        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);
        $claim->isInvalidTreatment = (!is_null($claim->activeTreatment->invalidated_reason) && !is_null($claim->activeTreatment->validated_at)) ? TRUE : FALSE;
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
    protected function treatmentClaim(Request $request, $claim)
    {
        $institution = $this->institution();

        $staff = $this->staff();

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);

        $rules = [
            'amount_returned' => [
                'nullable',
                'filled',
                'integer',
                Rule::requiredIf(!is_null($claim->amount_disputed) && !is_null($claim->amount_currency_slug)),
                'min:0'
            ],
            'solution' => ['required', 'string'],
            'comments' => ['nullable', 'string'],
            'preventive_measures' => [
                'string',
                Rule::requiredIf(!is_null(Metadata::where('name', 'measure-preventive')->firstOrFail()->data)
                    && Metadata::where('name', 'measure-preventive')->firstOrFail()->data == 'true')
            ],
            'can_communicate'
        ];

        $this->validate($request, $rules);

        $claim->activeTreatment->update([
            'amount_returned' => $request->amount_returned,
            'solution' => $request->solution,
            'comments' => $request->comments,
            'preventive_measures' => $request->preventive_measures,
            'solved_at' => Carbon::now(),
            'unfounded_reason' => NULL
        ]);

        if (isEscalationClaim($claim)) {
            $claim->update(['escalation_status' => 'treated']);
        } else {
            $claim->update(['status' => 'treated']);
        }

        $this->activityLogService->store(
            "Traitement d'une réclamation",
            $this->institution()->id,
            $this->activityLogService::TREATMENT_CLAIM,
            'claim',
            $this->user(),
            $claim
        );
        $configuration  = $this->nowConfiguration()['configuration'];
        $lead_pilot  = $this->nowConfiguration()['lead_pilot'];
        $all_active_pilots  = $this->nowConfiguration()['all_active_pilots'];
        $responsible_pilot  = null;

        if ($configuration->many_active_pilot  === "0") {
            // one active pivot
            if (!is_null($this->getInstitutionPilot($institution))) {
                $this->getInstitutionPilot($institution)->notify(new TreatAClaim($claim));
            }
        } else if ($configuration->many_active_pilot  === "1") {
            // many active pivot
            foreach ($all_active_pilots as $pilot) {
                if ($pilot->staff->id == $claim->activeTreatment->transferred_to_unit_by) {
                    $responsible_pilot =  $pilot->staff;
                }
            }
            if ($lead_pilot->identite) {
                $lead_pilot->identite->notify(new TreatAClaim($claim));
            }
            if ($responsible_pilot) {
                $responsible_pilot->identite->notify(new TreatAClaim($claim));
            }
        }

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
    protected function unfoundedClaim(Request $request, $claim)
    {

        $institution = $this->institution();
        $staff = $this->staff();

        $this->validate($request, $this->rules($staff, 'unfounded'));

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);

        $claim->activeTreatment->update([
            'unfounded_reason' => $request->unfounded_reason,
            'declared_unfounded_at' => Carbon::now(),
            'amount_returned' => NULL,
            'solution' => NULL,
            'comments' => NULL,
            'preventive_measures' => NULL,
        ]);

        $claim->update(['status' => 'treated']);

        $this->activityLogService->store(
            "Une réclamation a été déclarée non fondée",
            $this->institution()->id,
            $this->activityLogService::UNFOUNDED_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        if (!is_null($this->getInstitutionPilot($institution))) {
            $this->getInstitutionPilot($institution)->notify(new TreatAClaim($claim));
        }

        return response()->json($claim, 200);
    }
}
