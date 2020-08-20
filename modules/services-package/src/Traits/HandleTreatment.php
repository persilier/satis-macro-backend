<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Notifications\TransferredToUnit;

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
        $activeTreatment->update(['transferred_to_unit_at' => Carbon::now(), 'responsible_unit_id' => $request->unit_id, 'rejected_reason' => NULL,
            'rejected_at' => NULL]);
        $claim->update(['status' => 'transferred_to_unit']);

        \Illuminate\Support\Facades\Notification::send($this->getUnitStaffIdentities($request->unit_id), new TransferredToUnit($claim));

        return $claim;
    }

}