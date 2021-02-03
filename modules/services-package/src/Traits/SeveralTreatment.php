<?php


namespace Satis2020\ServicePackage\Traits;


/**
 * Trait SeveralTreatment
 * @package Satis2020\ServicePackage\Traits
 */
trait SeveralTreatment
{


    /**
     * @param $claim
     * @return array
     */
    protected function backupData($claim){

        if(!$treatments = $claim->activeTreatment->treatments){
            $treatments = collect([]);
        }

        $treatments->push([
            'invalidated_reason' => $claim->activeTreatment->invalidated_reason,
            'validated_at' => $claim->activeTreatment->validated_at,
            'declared_unfounded_at' => $claim->activeTreatment->declared_unfounded_at,
            'unfounded_reason' => $claim->activeTreatment->unfounded_reason,
            'solved_at' => $claim->activeTreatment->solved_at,
            'amount_returned' => $claim->activeTreatment->amount_returned,
            'solution' => $claim->activeTreatment->solution,
            'preventive_measures' => $claim->activeTreatment->preventive_measures,
            'comments' => $claim->activeTreatment->comments,
        ]);

        return $treatments;

    }

}
