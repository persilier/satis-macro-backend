<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Claim;
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
        }else{
            if ($claim->status == Claim::CLAIM_UNSATISFIED){
                if ($activeTreatment->type == Treatment::NORMAL){
                    $activeTreatment = Treatment::create([
                        'claim_id' => $claim->id,
                        'type'=>Treatment::ESCALATION
                    ]);
                }
            }
        }
        $claim->update(['active_treatment_id' => $activeTreatment->id]);
        return $activeTreatment;
    }

    protected function transferToUnit($request, $claim,$sendNotif=true)
    {
        $activeTreatment = $this->retrieveOrCreateActiveTreatment($claim);

        $updateData = [
            'transferred_to_unit_at' => Carbon::now(),
            'transferred_to_unit_by' => $this->staff()->id,
            'responsible_unit_id' => $request->unit_id,
            'rejected_reason' => NULL,
            'rejected_at' => NULL,
        ];

        // set number reject to NULL if and only if the activeTreatment's responsible_unit_id is not equal to the request's unit_id
        if ($activeTreatment->responsible_unit_id != $request->unit_id) {
            $updateData['number_reject'] = NULL;
        }

        $activeTreatment->update($updateData);

        if ($claim->status == Claim::CLAIM_UNSATISFIED){
            $claim->update(['escalation_status' => 'transferred_to_unit']);
        }else{
            $claim->update(['status' => 'transferred_to_unit']);
        }

        if ($sendNotif){
            \Illuminate\Support\Facades\Notification::send($this->getUnitStaffIdentities($request->unit_id), new TransferredToUnit($claim));
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->store("Plainte transférée à une unité",
                $this->institution()->id,
                ActivityLogService::TRANSFER_TO_UNIT,
                'claim',
                $this->user(),
                $claim
            );
        }
        return $claim;
    }

}