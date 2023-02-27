<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;

/**
 * Trait SeveralSatisfactionMesured
 * @package Satis2020\ServicePackage\Traits
 */
trait SeveralSatisfactionMesured
{


    /**
     * @param $claim
     * @param array $validationData
     * @return array
     */
    protected function backupData($claim)
    {

        $satisfactionHistory = $claim->activeTreatment->satisfaction_history;
        if (is_null($satisfactionHistory)) {
            $satisfactionHistory = collect([]);
        } else {
            $satisfactionHistory = collect($satisfactionHistory);
        }
        
        $satisfactionHistory->push([
            'is_claimer_satisfied' => $claim->activeTreatment->is_claimer_satisfied,
            'satisfaction_measured_by' => $claim->activeTreatment->satisfaction_measured_by,
            'satisfaction_measured_at' => $claim->activeTreatment->satisfaction_measured_at,
            'unsatisfied_reason' => $claim->activeTreatment->unsatisfied_reason,
            'note' => $claim->activeTreatment->note
        ]);

        return $satisfactionHistory->all();
    }
}
