<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Channels\MessageChannel;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

trait Notification
{

    protected function getNotification($event)
    {
        return collect(json_decode(Metadata::ofName('notifications')->firstOrFail()->data))
            ->first(function ($item, $key) use ($event) {
                return $item->event == $event;
            });
    }

    protected function getFeedBackChannels($staff)
    {
        $channels = collect($staff->feedback_preferred_channels);

        return $channels->isEmpty()
            ? []
            : $channels->map(function ($item, $key) {
                return $item == 'sms' ? MessageChannel::class : 'mail';
            })->all();
    }

    protected function getInstitutionPilot($institution = null)
    {

        $roleName = 'pilot';

        if (!is_null($institution)) {
            if ($institution->institutionType->name == 'holding' && Role::where('name', 'pilot-holding')->where('guard_name', 'api')->exists()) {
                $roleName = 'pilot-holding';
            } elseif ($institution->institutionType->name == 'filiale' && Role::where('name', 'pilot-filial')->where('guard_name', 'api')->exists()) {
                $roleName = 'pilot-filial';
            }
        }

        return User::with('identite.staff')
            ->get()
            ->first(function ($value, $key) use ($institution, $roleName) {
                return is_null($institution)
                    ? $value->hasRole($roleName)
                    : $value->identite->staff->institution_id == $institution->id && $value->hasRole($roleName);
            })->identite;
    }

    protected function getStaffInstitutionMessageApi($institution)
    {
        return $institution->institutionType->name == 'membre'
            ? Institution::with('institutionMessageApi', 'institutionType')
                ->get()
                ->first(function ($value, $key) {
                    return $value->institutionType->name == 'observatory';
                })->institutionMessageApi
            : $institution->institutionMessageApi;
    }

    protected function getUnitStaffIdentities($unitId)
    {
        return Identite::with('staff.unit', 'user')
            ->get()
            ->filter(function ($value, $key) use ($unitId) {
                return is_null($value->staff->unit)
                    ? false
                    : $value->staff->unit->id == $unitId && $value->user->hasRole('staff');
            })
            ->values();
    }

    protected function getStaffIdentities($staffIds, $exceptIds = [])
    {
        return Identite::with('staff')
            ->get()
            ->filter(function ($value, $key) use ($staffIds, $exceptIds) {
                return is_null($value->staff)
                    ? false
                    : in_array($value->staff->id, $staffIds) && !in_array($value->staff->id, $exceptIds);
            })
            ->values();
    }

}