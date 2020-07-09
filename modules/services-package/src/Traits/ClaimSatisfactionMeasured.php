<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Claim;

/**
 * Trait ClaimSatisfactionMeasured
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimSatisfactionMeasured
{

    protected  function getClaim($institutionId){


        return $claims = Claim::with($this->getRelations())->where('institution_targeted_id', $institutionId)
            ->join('staff', function ($join) {
                $join->on('claims.created_by', '=', 'staff.id');
            })->join('treatments', function ($join){
                $join->on('claims.id', '=', 'treatments.claim_id')
                        ->on('claims.active_treatment_id', '=', 'treatments.id');
            })->where('staff.institution_id', $institutionId)->orWhere('claims.institution_targeted_id',$institutionId)
            ->where('claims.status', 'validated')->select('claims.*');

    }


    protected  function rules(){

        $data['is_claimer_satisfied'] = ['required', 'integer'];
        $data['unsatisfaction_reason'] = ['required', 'string'];

        return $data;
    }

    /**
     * @return array
     */
    protected function getRelations()
    {
        return [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment', 'satisfactionMeasuredBy.identite'
        ];
    }


}