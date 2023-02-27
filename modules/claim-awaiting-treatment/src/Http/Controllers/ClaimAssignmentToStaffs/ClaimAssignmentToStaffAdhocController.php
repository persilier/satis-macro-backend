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
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\SeveralTreatment;
use Satis2020\ServicePackage\Notifications\TreatAClaim;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;

/**
 * Class ClaimAssignmentToStaffController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAssignmentToStaffs
 */
class ClaimAssignmentToStaffAdhocController extends ApiController
{
    use ClaimAwaitingTreatment, ConfigurationPilotTrait, SeveralTreatment;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-assignment-to-staff')->only(['index']);
        //$this->middleware('permission:show-claim-assignment-to-staff')->only(['show', 'treatmentClaim', 'unfoundedClaim']);

        $this->activityLogService = $activityLogService;
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

        $claim = Claim::where('escalation_status', Claim::CLAIM_AT_DISCUSSION)
            ->whereHas('activeTreatment', function ($q) {
                $q->where('responsible_staff_id', $this->staff()->id);
            })
            ->whereNull('deleted_at')
            ->find($claim);

        //$this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);

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
            'can_communicate' => 'required',
            'solution_communicated' => ['required_if:can_communicate,' . '1', 'string'],
        ];


        $this->validate($request, $rules);
        if (!$claim) {
            return [
                'error' => true,
                'message' => "Can't retrieve the claim"
            ];
        }
        $validationData = [
            'invalidated_reason' => NULL,
            'validated_at' => Carbon::now(),
            'validated_by' => $this->staff()->id,
        ];


        $claim->activeTreatment->update([
            'amount_returned' => $request->amount_returned,
            'solution' => $request->solution,
            'comments' => $request->comments,
            'preventive_measures' => $request->preventive_measures,
            'solved_at' => Carbon::now(),
            'unfounded_reason' => NULL,
            'solution_communicated' => $request->solution_communicated,
        ]);

        $backup = $this->backupData($claim, $validationData);
        $claim->activeTreatment->update([
            'solution_communicated' => $request->solution_communicated,
            'validated_at' => Carbon::now(),
            'validated_by' => $this->staff()->id,
            'invalidated_reason' => NULL,
            'treatments' => $backup
        ]);

        if ((int)$request->can_communicate == 1) {
            $claim->update(['escalation_status' => Claim::CLAIM_VALIDATED]);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolution($claim));
        } else {
            $claim->update(['escalation_status' => Claim::CLAIM_RESOLVED]);
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
    protected function closedClaim(Request $request, $claim)
    {

        $institution = $this->institution();

        $staff = $this->staff();

        $claim = Claim::where('escalation_status', Claim::CLAIM_AT_DISCUSSION)
            ->whereHas('activeTreatment', function ($q) {
                $q->where('responsible_staff_id', $this->staff()->id);
            })
            ->whereNull('deleted_at')
            ->find($claim);

        //$this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);
        $rules = [
            'motif' => ['required', 'string'],
        ];

        $this->validate($request, $rules);
        if (!$claim) {
            return [
                'error' => true,
                'message' => "Can't retrieve the claim"
            ];
        }
        $claim->activeTreatment->update([
            'closed_reason' => $request->motif,
            'closed_by' => $this->staff()->id,
            'closed_at' => now(),
        ]);

        $claim->update([
            'escalation_status' => Claim::CLAIM_CLOSED,
            'closed_at' => now(),
        ]);

        $this->activityLogService->store(
            "Clôture d'une réclamation",
            $this->institution()->id,
            $this->activityLogService::TREATMENT_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim, 200);
    }
}
