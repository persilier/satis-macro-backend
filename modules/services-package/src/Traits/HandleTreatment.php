<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Notifications\TransferredToUnit;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

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

        $updateData = [
            'transferred_to_unit_at' => Carbon::now(),
            'responsible_unit_id' => $request->unit_id,
            'rejected_reason' => NULL,
            'rejected_at' => NULL,
        ];

        // set number reject to NULL if and only if the activeTreatment's responsible_unit_id is not equal to the request's unit_id
        if ($activeTreatment->responsible_unit_id != $request->unit_id) {
            $updateData['number_reject'] = NULL;
        }

        $activeTreatment->update($updateData);

        $claim->update(['status' => 'transferred_to_unit']);

        \Illuminate\Support\Facades\Notification::send($this->getUnitStaffIdentities($request->unit_id), new TransferredToUnit($claim));

        $activityLogService = app(ActivityLogService::class);
        $activityLogService->store("Plainte transférée à une unité",
            $this->institution()->id,
            ActivityLogService::TRANSFER_TO_UNIT,
            'claim',
            $this->user(),
            $claim
        );

        return $claim;
    }

}