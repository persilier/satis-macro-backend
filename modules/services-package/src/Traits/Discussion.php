<?php


namespace Satis2020\ServicePackage\Traits;


use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;

trait Discussion
{

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

        return Staff::with('identite.user')
            ->get()
            ->filter(function ($value, $key) use ($discussion) {

                $value->load('unit');

                if(is_null($discussion->createdBy) || is_null($value->identite)){

                    return false;

                }

                if(is_null($discussion->createdBy->unit) || is_null($value->identite->user)){

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
    }

}