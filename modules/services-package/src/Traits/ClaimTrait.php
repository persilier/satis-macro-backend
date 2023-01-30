<?php

namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\In;
use Satis2020\ServicePackage\Consts\Constants;
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
trait ClaimTrait
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
            ->leftJoin('staff', function ($join) {
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
                '(`treatments`.`transferred_to_unit_at` IS NOT NULL) and (`treatments`.`responsible_unit_id` = ?)',
                [$unitId]
            )
            ->whereNull('claims.deleted_at');
    }

    /**
     * @param $claimId
     * @param bool $withRelations
     * @return Builder|Builder[]|Collection|Model|null
     */
    protected function getOneClaimQuery($claimId,$withRelations=true)
    {
        $relations = $withRelations?$this->getRelations():[];
        return Claim::with($relations)->findOrFail($claimId);
    }

    /**
     * @return array
     */
    protected function getRelations()
    {
        return Constants::getClaimRelations();
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
            ->leftJoin('staff', function ($join) {
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

        if (!$claim = $this->getClaimsTreat($institutionId, $unitId, $staffId)->where('claims.id', $claim)->first())
            throw new CustomException("Impossible de récupérer cette réclammation");
        else
            return Claim::with($this->getRelationsAwitingTreatment())->find($claim->id);
    }

}
