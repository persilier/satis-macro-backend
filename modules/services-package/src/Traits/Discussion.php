<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;

trait Discussion
{
    use ConfigurationPilotTrait, Notification;

    protected function getPilotRoleName($unit_id)
    {
        $pilot = 'pilot';

        $unit = Unit::with('institution.institutionType')->findOrFail($unit_id);

        try {
            if ($unit->institution->institutionType->name == 'holding') {
                $pilot = 'pilot-holding';
            }

            if ($unit->institution->institutionType->name == 'filiale') {
                $pilot = 'pilot-filial';
            }
        } catch (\Exception $exception) {
        }

        return $pilot;
    }

    protected function getContributors($discussion)
    {

        $staff =  Staff::with('identite.user')
            ->get()
            ->filter(function ($value, $key) use ($discussion) {

                $value->load('unit');

                if (is_null($discussion->createdBy) || is_null($value->identite)) {

                    return false;
                }

                if (is_null($discussion->createdBy->unit) || is_null($value->identite->user)) {

                    return false;
                }

                return is_null($discussion->createdBy->unit->institution_id) // en gros, si on est dans un hub

                    ? (($value->unit_id == $discussion->createdBy->unit_id && $value->identite->user->hasRole('staff'))
                        || $value->identite->user->hasRole($this->getPilotRoleName($discussion->createdBy->unit_id)))
                    && $discussion->staff->search(function ($item, $key) use ($value) {
                        return $item->id == $value->id;
                    }) === false

                    : (($value->unit_id == $discussion->createdBy->unit_id && $value->identite->user->hasRole('staff'))
                        || ($value->institution_id == $discussion->createdBy->institution_id && $value->identite->user->hasRole($this->getPilotRoleName($discussion->createdBy->unit_id))))
                    && $discussion->staff->search(function ($item, $key) use ($value) {
                        return $item->id == $value->id;
                    }) === false;
            });

        return $staff;
    }
    protected function getContributorsWithClaimCreator($discussion)
    {


        $staffs =  Staff::with('identite.user')
            ->get()
            ->filter(function ($value, $key) use ($discussion) {

                $value->load('unit');

                if (is_null($discussion->createdBy) || is_null($value->identite)) {

                    return false;
                }

                if (is_null($discussion->createdBy->unit) || is_null($value->identite->user)) {

                    return false;
                }

                return is_null($discussion->createdBy->unit->institution_id) // en gros, si on est dans un hub

                    ? (($value->unit_id == $discussion->claim->activeTreatment->responsible_unit_id && $value->identite->user->hasRole('staff'))
                        || $value->identite->user->hasRole($this->getPilotRoleName($discussion->createdBy->unit_id)))
                    && $discussion->staff->search(function ($item, $key) use ($value) {
                        return $item->id == $value->id;
                    }) === false

                    : (($value->unit_id == $discussion->claim->activeTreatment->responsible_unit_id && $value->identite->user->hasRole('staff'))
                        || ($value->institution_id == $discussion->createdBy->institution_id && $value->identite->user->hasRole($this->getPilotRoleName($discussion->createdBy->unit_id))))
                    && $discussion->staff->search(function ($item, $key) use ($value) {
                        return $item->id == $value->id;
                    }) === false;
            });
        $claim_creator = $discussion->claim->createdBy->load('identite.user');
        if (
            !$staffs->pluck('id')->contains($claim_creator->id) &&
            ($discussion->staff->search(function ($item, $key) use ($claim_creator) {
                return $item->id == $claim_creator->id;
            }) === false)
        ) {
            $staffs->push($claim_creator);
        }
        return $staffs;
    }
    public function addContributorsAtEscalade($discussion, $baseContributeors)
    {
        if (isEscalationClaim($discussion->claim)) {

            // ajouter les pilotes actove dans le cadre de l'escalde
            $configuration = $this->nowConfiguration();
            if ($configuration['configuration']['many_active_pilot'] === "1") {
                $actif_pilots  = $configuration['all_active_pilots']->pluck('staff');
                foreach ($actif_pilots as $actif_pilot) {
                    $actif_pilot->load('identite.user');
                    if (
                        !$baseContributeors->pluck('id')->contains($actif_pilot->id) &&
                        ($discussion->staff->search(function ($item, $key) use ($actif_pilot) {
                            return $item->id == $actif_pilot->id;
                        }) === false)
                    ) {
                        $baseContributeors->push($actif_pilot);
                    }
                }
            } else {
                $actif_pilot = $this->getInstitutionPilot($this->institution()->id)->identite->staff->load('identite.user');
                if (
                    !$baseContributeors->pluck('id')->contains($actif_pilot->id) &&
                    ($discussion->staff->search(function ($item, $key) use ($actif_pilot) {
                        return $item->id == $actif_pilot->id;
                    }) === false)
                ) {
                    $baseContributeors->push($actif_pilot);
                }
            }
        }

        // ajouter le staff responsable du traitement précédent de la réclamation
        $responsible_staff = $discussion->claim->activeTreatment->responsibleStaff ? $discussion->claim->activeTreatment->responsibleStaff->load('identite.user') : null;
        if (
            !is_null($responsible_staff) &&
            !$baseContributeors->contains($responsible_staff) &&
            ($discussion->staff->search(function ($item, $key) use ($responsible_staff) {
                return $item->id == $responsible_staff->id;
            }) === false)
        ) {
            $baseContributeors->push($responsible_staff);
        }

        // ajouter le lead de l'unite de traitment 
        $lead =  $responsible_staff->unit->lead ? $responsible_staff->unit->lead->load('identite.user') : null;
        if (
            !is_null($lead) &&
            !$baseContributeors->pluck('id')->contains($lead->id) &&
            ($discussion->staff->search(function ($item, $key) use ($lead) {
                return $item->id == $lead->id;
            }) === false)
        ) {
            $baseContributeors->push($lead);
        }

        return $baseContributeors;
    }
}
