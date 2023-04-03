<?php

namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\In;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Notifications\RejectAClaim;
use Satis2020\ServicePackage\Repositories\TreatmentRepository;

/**
 * Trait ClaimAwaitingTreatment
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimAwaitingTreatment
{
    /**
     * @param $institutionId
     * @param $unitId
     * @param string $statusColumn
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getClaimsQuery($institutionId, $unitId, $statusColumn = "status")
    {
        $claims = Claim::query()
            ->select('claims.*')
            ->leftJoin('staff', function ($join) {
                $join->on('claims.created_by', '=', 'staff.id');
            })
            ->join('treatments', function ($join) {
                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })
            ->whereRaw(
                '( (`staff`.`institution_id` = ? and `claims`.`' . $statusColumn . '` = ?) or (`claims`.`institution_targeted_id` = ? and `claims`.`' . $statusColumn . '` = ?) )',
                [$institutionId, 'transferred_to_unit', $institutionId, 'transferred_to_unit']
            )->whereRaw(
                '(`treatments`.`transferred_to_unit_at` IS NOT NULL) and (`treatments`.`responsible_unit_id` = ?)',
                [$unitId]
            )
            ->whereNull('claims.deleted_at')
            ->whereNull('treatment_board_id')
            ->with($this->getRelationsAwitingTreatment());
        return $claims;
    }
    /**
     * @param $unitId
     * @param $claim
     * @return Builder|Builder[]|Collection|Model|null
     * @throws CustomException
     */
    protected function getOneClaimQuery($unitId, $claim)
    {
        $claim = Claim::with($this->getRelationsAwitingTreatment())->findOrFail($claim);
        try {

            if (isEscalationClaim($claim)) {
                if ($claim->activeTreatment->responsible_unit_id != $unitId || $claim->escalation_status != "transferred_to_unit") {

                    throw new CustomException(__('messages.cant_get_claim', [], getAppLang()));
                }
            } else {
                if ($claim->activeTreatment->responsible_unit_id != $unitId || $claim->status != "transferred_to_unit") {

                    throw new CustomException(__('messages.cant_get_claim', [], getAppLang()));
                }
            }
        } catch (\Exception $exception) {

            throw new CustomException(__('messages.cant_get_claim', [], getAppLang()));
        }
        return $claim;
    }
    /**
     * @param $staff
     * @return bool
     */
    protected function checkLead($staff)
    {
        if (Unit::where('lead_id', $staff->id)->find($staff->unit_id)) {
            return true;
        }
        return false;
    }
    /**
     * @param $claim
     * @param $staffId
     * @return mixed
     */
    protected function assignmentClaim($claim, $staffId)
    {
        $claim->activeTreatment->update(['responsible_staff_id' => $staffId, 'assigned_to_staff_by' => $this->staff()->id, 'assigned_to_staff_at' => Carbon::now()]);

        if (isEscalationClaim($claim)) {
            $claim->update(['escalation_status' => 'assigned_to_staff']);
        } else {
            $claim->update(['status' => 'assigned_to_staff']);
        }

        return $claim;
    }
    /**
     * @param $claim
     * @param $request
     * @return mixed
     */
    protected function rejectedClaimUpdate($claim, $request)
    {
        $claim->activeTreatment->update([
            'transferred_to_unit_at' => NULL,
            'rejected_reason' => $request->rejected_reason,
            'rejected_at' => Carbon::now(),
            'number_reject' => (int) $claim->activeTreatment->number_reject + 1,
        ]);

        $statusColumn = isEscalationClaim($claim) ? "escalation_status" : "status";
        if (!is_null($claim->transfered_to_targeted_institution_at)) {
            $claim->update([$statusColumn => 'transferred_to_targeted_institution']);
            $institution = Institution::find($claim->institution_targeted_id);
        } else {
            $claim->update([$statusColumn => 'full']);
            $institution = is_null($claim->createdBy) ? $claim->institutionTargeted : $claim->createdBy->institution;
        }
        if (!is_null($this->getInstitutionPilot($institution))) {
            $this->getInstitutionPilot($institution)->notify(new RejectAClaim($claim));
        }
        try {
            \Illuminate\Support\Facades\Notification::send($this->getUnitStaffIdentities($claim->activeTreatment->responsible_unit_id), new RejectAClaim($claim));
        } catch (\Exception $exception) {
        }
        return $claim;
    }
    /**
     * @return array
     */
    protected function getRelationsAwitingTreatment()
    {
        return [
            'claimObject.claimCategory',
            'claimer',
            'relationship',
            'accountTargeted',
            'institutionTargeted',
            'unitTargeted',
            'requestChannel',
            'responseChannel',
            'amountCurrency',
            'createdBy.identite',
            'completedBy.identite',
            'files',
            'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite',
            'activeTreatment.assignedToStaffBy.identite',
            'activeTreatment.responsibleUnit'
        ];
    }
    /**
     * @param $staff
     * @param string $assignment
     * @return mixed
     */
    protected function rules($staff, $assignment = 'assignment')
    {
        if ($assignment === 'assignment') {
            $data['staff_id'] = ['required', Rule::exists('staff', 'id')->where(function ($query) use ($staff) {
                $query->where('unit_id', $staff->unit_id);
            })];
        }
        if ($assignment === 'unfounded') {
            $data['unfounded_reason'] = ['required', 'string'];
        }
        if ($assignment === 'rejected') {
            $data['rejected_reason'] = ['required', 'string'];
        }
        if ($assignment === 'treatment') {
            $data['amount_returned'] = ['nullable', 'filled', 'integer'];
            $data['solution'] = ['required', 'string'];
            $data['comments'] = ['required', 'string'];
            $data['preventive_measures'] = ['string'];
        }
        return $data;
    }
    /**
     * @param $institutionId
     * @param $unitId
     * @param $staffId
     * @param string $statusColumn
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getClaimsTreat($institutionId, $unitId, $staffId, $statusColumn = "status")
    {
        return DB::table('claims')
            ->select('claims.*')
            ->leftJoin('staff', function ($join) {
                $join->on('claims.created_by', '=', 'staff.id');
            })
            ->join('treatments', function ($join) {
                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })
            ->whereRaw(
                '( (`staff`.`institution_id` = ? and `claims`.`' . $statusColumn . '` = ?) or (`claims`.`institution_targeted_id` = ? and `claims`.`' . $statusColumn . '` = ?) )',
                [$institutionId, 'assigned_to_staff', $institutionId, 'assigned_to_staff']
            )->whereRaw(
                '(`treatments`.`transferred_to_unit_at` IS NOT NULL) and (`treatments`.`responsible_unit_id` = ?) and (`treatments`.`responsible_staff_id` = ?) and (`treatments`.`assigned_to_staff_at` IS NOT NULL)',
                [$unitId, $staffId]
            )
            ->whereNull('claims.deleted_at');
    }
    /**
     * @param $institutionId
     * @param $unitId
     * @param $staffId
     * @param $claim
     * @return Builder|Builder[]|Collection|Model|null
     * @throws CustomException
     */
    protected function getOneClaimQueryTreat($institutionId, $unitId, $staffId, $claim)
    {

        $claim = Claim::query()->find($claim);
        $statusColumn = isEscalationClaim($claim) ? "escalation_status" : "status";

        if (!$claim = $this->getClaimsTreat($institutionId, $unitId, $staffId, $statusColumn)->where('claims.id', $claim->id)->first())
            throw new CustomException(__('messages.cant_get_claim', [], getAppLang()));
        else
            return Claim::with($this->getRelationsAwitingTreatment())->find($claim->id);
    }
    protected function getTargetedStaffFromUnit($unitId)
    {
        return Staff::with('identite.user')
            ->where('unit_id', $unitId)
            ->get()
            ->filter(function ($value, $key) {
                if (is_null($value->identite)) {
                    return false;
                }
                if (is_null($value->identite->user)) {
                    return false;
                }
                return $value->identite->user->hasRole('staff');
            })
            ->values();
    }
    protected function getTargetedStaffFromUnitForReassignment($unitId, $responsibleStaffId)
    {
        return Staff::with('identite.user')
            ->where('unit_id', $unitId)
            ->where('id', '!=', $responsibleStaffId)
            ->get()
            ->filter(function ($value, $key) use ($responsibleStaffId) {
                if (is_null($value->identite)) {
                    return false;
                }
                if (is_null($value->identite->user)) {
                    return false;
                }
                return $value->identite->user->hasRole('staff');
            })
            ->values();
    }
    protected function canRejectClaim($claim)
    {
        $claim->load(['activeTreatment']);
        try {
            $settings = json_decode(Metadata::ofName('reject-unit-transfer-limitation')->firstOrFail()->data);
            $numberReject = (int)$claim->activeTreatment->number_reject;
        } catch (\Exception $exception) {
            return false;
        }
        $numberRejectMax = (int)$settings->number_reject_max;
        return $numberReject < $numberRejectMax;
    }
    /**
     * @param string $type
     * @return mixed
     */
    protected function queryClaimReassignment($type = "normal")
    {

        $statusColumn = $type == Claim::CLAIM_UNSATISFIED ? "escalation_status" : "status";

        return Claim::with($this->getRelationsAwitingTreatment())
            ->when($type == Claim::CLAIM_UNSATISFIED, function ($query) {
                $query->where('status', Claim::CLAIM_UNSATISFIED);
            })
            ->whereHas('activeTreatment', function ($query) {

                $query->where('responsible_staff_id', '!=', NULL)->where('responsible_unit_id', $this->staff()->unit_id);
            })->where($statusColumn, 'assigned_to_staff');
    }
    protected function checkLeadReassignment()
    {
        $staff = $this->staff();
        if (!$this->checkLead($staff)) {
            throw new CustomException(__('messages.only_lead_can_is_allow', [], getAppLang()));
        }
    }

    protected function getNormalTreatment($claimId)
    {
        return (new TreatmentRepository())->getByClaimId($claimId, Treatment::NORMAL);
    }
}
