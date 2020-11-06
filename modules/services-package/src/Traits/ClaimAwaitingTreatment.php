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
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Notifications\RejectAClaim;

/**
 * Trait ClaimAwaitingTreatment
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimAwaitingTreatment
{

    /**
     * @param $institutionId
     * @param $unitId
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getClaimsQuery($institutionId, $unitId)
    {
        return DB::table('claims')
            ->select('claims.*')
            ->join('staff', function ($join) {
                $join->on('claims.created_by', '=', 'staff.id');
            })
            ->join('treatments', function ($join) {
                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })
            ->whereRaw(
                '( (`staff`.`institution_id` = ? and `claims`.`status` = ?) or (`claims`.`institution_targeted_id` = ? and `claims`.`status` = ?) )',
                [$institutionId, 'transferred_to_unit', $institutionId, 'transferred_to_unit']
            )->whereRaw(
                '(`treatments`.`transferred_to_unit_at` != ?) and (`treatments`.`responsible_unit_id` = ?)',
                ['NULL', $unitId]
            )
            ->whereNull('claims.deleted_at');
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

            if ($claim->activeTreatment->responsible_unit_id != $unitId || $claim->status != "transferred_to_unit") {

                throw new CustomException("Impossible de traiter cette réclammation");
            }

        } catch (\Exception $exception) {

            throw new CustomException("Imposible de récupérer cette réclammation.");

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

        $claim->update(['status' => 'assigned_to_staff']);

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
            'number_reject' => (int)$claim->activeTreatment->number_reject + 1
        ]);

        if (!is_null($claim->transfered_to_targeted_institution_at)) {
            $claim->update(['status' => 'transferred_to_targeted_institution']);
            $institution = Institution::find($claim->institution_targeted_id);
        } else {
            $claim->update(['status' => 'full']);
            $institution = $claim->createdBy->institution;
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
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment'
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
            $data['amount_returned'] = ['required', 'integer'];
            $data['solution'] = ['required', 'string'];
            $data['comments'] = ['required', 'string'];
            $data['preventive_measures'] = ['required', 'string'];
        }

        return $data;
    }


    /**
     * @param $institutionId
     * @param $unitId
     * @param $staffId
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getClaimsTreat($institutionId, $unitId, $staffId)
    {
        return DB::table('claims')
            ->select('claims.*')
            ->join('staff', function ($join) {
                $join->on('claims.created_by', '=', 'staff.id');
            })
            ->join('treatments', function ($join) {
                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })
            ->whereRaw(
                '( (`staff`.`institution_id` = ? and `claims`.`status` = ?) or (`claims`.`institution_targeted_id` = ? and `claims`.`status` = ?) )',
                [$institutionId, 'assigned_to_staff', $institutionId, 'assigned_to_staff']
            )->whereRaw(
                '(`treatments`.`transferred_to_unit_at` != ?) and (`treatments`.`responsible_unit_id` = ?) and (`treatments`.`responsible_staff_id` = ?) and (`treatments`.`assigned_to_staff_at` != ?)',
                ['NULL', $unitId, $staffId, 'NULL']
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

        if (!$claim = $this->getClaimsTreat($institutionId, $unitId, $staffId)->where('claims.id', $claim)->first())
            throw new CustomException("Impossible de récupérer cette réclammation");
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

    protected function canRejectClaim($claim)
    {
        $claim->load(['activeTreatment']);

        try {
            $settings = json_decode(Metadata::ofName('reject-unit-transfer-limitation')->firstOrFail()->data);
            $nombreReject = (int)$claim->activeTreatment->number_reject;
        } catch (\Exception $exception) {
            return false;
        }

        $nombreRejectMax = (int)$settings->number_reject_max;

        return $nombreReject <= $nombreRejectMax;
    }

}