<?php
namespace Satis2020\ServicePackage\Traits;
use Carbon\Carbon;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;

trait ClaimAwaitingTreatment
{
    protected function getClaimsQuery($institutionId, $unitId){
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
                [$institutionId, 'full', $institutionId, 'transferred_to_targeted_institution']
            )->whereRaw(
                '(`treatments`.`transferred_to_unit_at` != ?) and (`treatments`.`responsible_unit_id` = ?)',
                ['NULL',$unitId]
            )
            ->whereNull('claims.deleted_at');
    }

    protected function getOneClaimQuery($institutionId, $unitId, $claim){

        if(!$claim = $this->getClaimsQuery($institutionId, $unitId)->where('claims.id', $claim)->first())
            throw new CustomException("Impossible de récupérer cette réclammation");
        else
            return Claim::with($this->getRelationsAwitingTreatment())->find($claim->id);
    }

    protected function checkLead($staff){
        $unit = $staff->load('unit')->unit;

        if($unit->lead === $staff->unit_id){
            return true;
        }

        return false;
    }

    protected function assignmentClaim($claim, $staffId){
        $claim->activeTreatment->update(['responsible_staff_id' => $staffId, 'assigned_to_staff_at'=> Carbon::now()]);

        $claim->update(['status' => 'assigned_to_staff']);

        return $claim;
    }

    protected function rejectedClaimUpdate($claim, $request){

        $claim->activeTreatment->update(['transferred_to_unit_at' => null, 'rejected_reason' => $request->rejected_reason, 'rejected_at' => Carbon::now()]);

        if(!is_null($claim->transfered_to_targeted_institution_at)){
            $claim->update(['status', 'transferred_to_institution']);
        }else{
            $claim->update(['status', 'full']);
        }

        return $claim;
    }

    protected function getRelationsAwitingTreatment()
    {
        return [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment'
        ];
    }

    protected  function rules($staff, $assignment = 'assignment'){

        if($assignment === 'assignment'){
            $data['staff_id'] = [ 'required', Rule::exists('staff', 'id')->where(function ($query) use ($staff){
                $query->where('unit_id', $staff->unit_id);
            })];
        }

        if($assignment === 'unfounded'){
            $data['unfounded_reason'] = ['required', 'string'];
        }

        if($assignment === 'rejected'){
            $data['rejected_reason'] = ['required', 'string'];
        }

        if($assignment === 'treatment'){
            $data['amount_returned'] = ['required', 'integer'];
            $data['solution'] = ['required', 'string'];
            $data['comments'] = ['required', 'string'];
            $data['preventive_measures'] = ['required', 'string'];
        }

        return $data;
    }


    protected function getClaimsTreat($institutionId, $unitId, $staffId){
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

    protected function getOneClaimQueryTreat($institutionId, $unitId, $staffId,  $claim){

        if(!$claim = $this->getClaimsTreat($institutionId, $unitId, $staffId)->where('claims.id', $claim)->first())
            throw new CustomException("Impossible de récupérer cette réclammation");
        else
            return Claim::with($this->getRelationsAwitingTreatment())->find($claim->id);
    }

}