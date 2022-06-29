<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Treatment;

trait AwaitingValidation
{
    protected function getClaimsAwaitingValidationInMyInstitution($institution_id = null,$type="normal")
    {
        $institution_id = is_null($institution_id)
            ? $this->institution()->id
            : $institution_id;

        $statusColumn = $type==Treatment::ESCALATION?"status":"escalation_status";

        $claimsTreated = Claim::with($this->getRelations())->where($statusColumn, 'treated')->get();
        return $claimsTreated->filter(function ($value, $key) use ($institution_id) {
            $value->activeTreatment->load($this->getActiveTreatmentRelations());
            return $value->activeTreatment->responsibleStaff->institution_id == $institution_id;
        });
    }

    /**
     * @return array
     */
    protected function getRelations()
    {
        return [
            'claimObject.claimCategory',
            'claimer',
            'relationship',
            'accountTargeted',
            'institutionTargeted',
            'unitTargeted',
            'requestChannel',
            'responseChannel',
            'amountCurrency',
            'createdBy.identite',
            'completedBy.identite',
            'files',
            'activeTreatment'
        ];
    }

    protected function getActiveTreatmentRelations()
    {
        return [
            'responsibleUnit',
            'assignedToStaffBy.identite',
            'responsibleStaff.identite',
            'satisfactionMeasuredBy.identite',
        ];
    }

    /**
     * @param $request
     * @param $claim
     * @return mixed
     */
    protected function handleValidate($request, $claim)
    {
        $validationData = [
            'invalidated_reason' => NULL,
            'validated_at' => Carbon::now(),
            'validated_by' => $this->staff()->id,
        ];

        $backup = $this->backupData($claim, $validationData);

        $claim->activeTreatment->update([
            'solution_communicated' => $request->solution_communicated,
            'validated_at' => Carbon::now(),
            'validated_by' => $this->staff()->id,
            'invalidated_reason' => NULL,
            'treatments' => $backup
        ]);

        if(!is_null($claim->activeTreatment->declared_unfounded_at)){
            // the claim is declared unfounded
            $claim->update(['status' => 'archived']);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolutionUnfounded($claim));
        } else { // the claim is solved
            if (isEscalationClaim($claim)){
                $claim->update(['escalation_status' => 'validated']);
            }else{
                $claim->update(['status' => 'validated']);
            }
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolution($claim));
        }

        $this->activityLogService->store("Une réclamation a été validée",
            $this->institution()->id,
            $this->activityLogService::VALIDATED_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        if (!is_null($claim->activeTreatment->responsibleStaff)) {
            if (!is_null($claim->activeTreatment->responsibleStaff->identite)) {
                $claim->activeTreatment->responsibleStaff->identite->notify(new \Satis2020\ServicePackage\Notifications\ValidateATreatment($claim));
            }
        }

        return $claim;
    }

    protected function handleInvalidate($request, $claim)
    {
        $validationData = [
            'invalidated_reason' => $request->invalidated_reason,
            'validated_at' => Carbon::now()
        ];

        $backup = $this->backupData($claim, $validationData);

        $claim->activeTreatment->update([
            'invalidated_reason' => $validationData['invalidated_reason'],
            'validated_at' => $validationData['validated_at'],
            'solved_at' => NULL,
            'declared_unfounded_at' => NULL,
            'treatments' => $backup
        ]);

        if(isEscalationClaim($claim)){
            $claim->update(['escalation_status' => 'assigned_to_staff']);
        }else{
            $claim->update(['status' => 'assigned_to_staff']);
        }

        if (!is_null($claim->activeTreatment->responsibleStaff)) {
            if (!is_null($claim->activeTreatment->responsibleStaff->identite)) {
                $claim->activeTreatment->responsibleStaff->identite->notify(new \Satis2020\ServicePackage\Notifications\InvalidateATreatment($claim));
            }
        }

        $this->activityLogService->store("Une réclamation a été invalide",
            $this->institution()->id,
            $this->activityLogService::INVALIDATED_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        return $claim;
    }

    protected function showClaim($claim)
    {
        $claim->load($this->getRelations());
        $claim->activeTreatment->load($this->getActiveTreatmentRelations());
        return $claim;
    }
}
