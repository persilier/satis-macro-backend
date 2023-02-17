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
        $satisfactionMesured = $claim->activeTreatment->satisfactionMesured;

        // If treatments is null, initialize it at empty array
        if (is_null($satisfactionMesured)) {
            $satisfactionMesured = collect([]);
        } else {
            $satisfactionMesured = collect($satisfactionMesured);
        }

        $satisfactionMesured->push([
            'is_claimer_satisfied' => $claim->activeTreatment->is_claimer_satisfied,
            'satisfaction_measured_by' => $claim->activeTreatment->satisfaction_measured_by,
            'satisfaction_measured_at' => $claim->activeTreatment->satisfaction_measured_at,
            'unsatisfied_reason' => $claim->activeTreatment->unsatisfied_reason,
            'note' => $claim->activeTreatment->note
        ]);

        return $satisfactionMesured->all();
    }
}
