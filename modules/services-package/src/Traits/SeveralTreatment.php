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
     * @param array $validationData
     * @return array
     */
    protected function backupData($claim, array $validationData)
    {
        $treatments = $claim->activeTreatment->treatments;

        // If treatments is null, initialize it at empty array
        if (is_null($treatments)) {
            $treatments = collect([]);
        } else {
            $treatments = collect($treatments);
        }

        $treatments->push([
            'invalidated_reason' => $validationData['invalidated_reason'],
            'validated_at' => $validationData['validated_at'],
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
