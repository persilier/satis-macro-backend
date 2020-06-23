<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Treatment;

trait HandleTreatment
{

    protected function retrieveOrCreateActiveTreatment($claim)
    {
        $claim->load('activeTreatment');
        $activeTreatment = $claim->activeTreatment;
        if (is_null($activeTreatment)) {
            $activeTreatment = Treatment::create(['claim_id' => $claim->id]);
        }
        $claim->update(['active_treatment_id' => $activeTreatment->id]);
        return $activeTreatment;
    }

    protected function transferToUnit($request, $claim)
    {
        $activeTreatment = $this->retrieveOrCreateActiveTreatment($claim);
        $activeTreatment->update(['transferred_to_unit_at' => Carbon::now(), 'responsible_unit_id' => $request->unit_id]);
        $claim->update(['status' => 'transferred_to_unit']);
        return $claim;
    }

}