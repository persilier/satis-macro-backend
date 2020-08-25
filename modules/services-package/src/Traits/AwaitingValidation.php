<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Claim;

trait AwaitingValidation
{
    protected function getClaimsAwaitingValidationInMyInstitution($institution_id = null)
    {
        $institution_id = is_null($institution_id)
            ? $this->institution()->id
            : $institution_id;

        $claimsTreated = Claim::with($this->getRelations())->where('status', 'treated')->get();
        return $claimsTreated->filter(function ($value, $key) use ($institution_id) {
            $value->activeTreatment->load($this->getActiveTreatmentRelations());
            return $value->activeTreatment->responsibleStaff->institution_id == $institution_id;
        });
    }

    protected function getRelations()
    {
        return [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment'
        ];
    }

    protected function getActiveTreatmentRelations()
    {
        return [
            'responsibleUnit', 'assignedToStaffBy.identite', 'responsibleStaff.identite'
        ];
    }

    protected function handleValidate($request, $claim)
    {
        $claim->activeTreatment->update([
            'solution_communicated' => $request->solution_communicated,
            'validated_at' => Carbon::now(),
            'invalidated_reason' => NULL
        ]);

        if (!is_null($claim->activeTreatment->declared_unfounded_at)) { // the claim is declared unfounded
            $claim->update(['status' => 'archived']);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolutionUnfounded($claim));
        } else { // the claim is solved
            $claim->update(['status' => 'validated']);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolution($claim));
        }

        if (!is_null($claim->activeTreatment->responsibleStaff)) {
            if (!is_null($claim->activeTreatment->responsibleStaff->identite)) {
                $claim->activeTreatment->responsibleStaff->identite->notify(new \Satis2020\ServicePackage\Notifications\ValidateATreatment($claim));
            }
        }


        return $claim;
    }

    protected function handleInvalidate($request, $claim)
    {
        $claim->activeTreatment->update([
            'invalidated_reason' => $request->invalidated_reason,
            'validated_at' => Carbon::now(),
            'solved_at' => NULL,
            'declared_unfounded_at' => NULL,
        ]);

        $claim->update(['status' => 'assigned_to_staff']);

        if (!is_null($claim->activeTreatment->responsibleStaff)) {
            if (!is_null($claim->activeTreatment->responsibleStaff->identite)) {
                $claim->activeTreatment->responsibleStaff->identite->notify(new \Satis2020\ServicePackage\Notifications\InvalidateATreatment($claim));
            }
        }


        return $claim;
    }

    protected function showClaim($claim)
    {
        $claim->load($this->getRelations());
        $claim->activeTreatment->load($this->getActiveTreatmentRelations());
        return $claim;
    }
}