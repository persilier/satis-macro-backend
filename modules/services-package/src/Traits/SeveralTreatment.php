<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;

/**
 * Trait SeveralTreatment
 * @package Satis2020\ServicePackage\Traits
 */
trait SeveralTreatment
{


    /**
     * @param $claim
     * @param $request
     * @param bool $rejected
     * @param bool $validated
     * @return array
     */
    protected function backupData($claim, $request, $rejected = true, $validated = false){

        if(!$treatments = $claim->activeTreatment->treatments){
            $treatments = collect([]);
        }else {
            $treatments = collect($treatments);
        }

        $treatments->push([
            'invalidated_reason' => $rejected ? $claim->activeTreatment->invalidated_reason : ($validated ? NULL : $request->invalidated_reason),
            'rejected_reason' => $rejected ? $request->rejected_reason : NULL,
            'rejected_at' => $rejected ? Carbon::now() : NULL,
            'validated_at' => $rejected ? $claim->activeTreatment->validated_at : Carbon::now() ,
            'declared_unfounded_at' => $claim->activeTreatment->declared_unfounded_at,
            'unfounded_reason' => $claim->activeTreatment->unfounded_reason,
            'solved_at' => $claim->activeTreatment->solved_at,
            'amount_returned' => $claim->activeTreatment->amount_returned,
            'solution' => $claim->activeTreatment->solution,
            'preventive_measures' => $claim->activeTreatment->preventive_measures,
            'comments' => $claim->activeTreatment->comments,
        ]);

        return $treatments->all();

    }

}
