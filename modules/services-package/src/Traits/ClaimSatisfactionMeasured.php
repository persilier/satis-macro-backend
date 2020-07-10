<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Claim;

/**
 * Trait ClaimSatisfactionMeasured
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimSatisfactionMeasured
{

    /**
     * @param $institutionId
     * @param string $status
     * @return Builder
     */
    protected  function getClaim($institutionId, $status = 'validated'){


        return $claims = Claim::with($this->getRelations())->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id');
        })->where('claims.institution_targeted_id',$institutionId)->where('claims.status', $status)->select('claims.*');

    }


    protected  function rules(){

        $data['is_claimer_satisfied'] = ['required', 'boolean'];
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
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment.satisfactionMeasuredBy.identite'
        ];
    }


}