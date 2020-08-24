<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Channels\MessageChannel;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Staff;
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

        try {
            return User::with('identite.staff')
                ->get()
                ->first(function ($value, $key) use ($institution, $roleName) {
                    return is_null($institution)
                        ? $value->hasRole($roleName)
                        : $value->identite->staff->institution_id == $institution->id && $value->hasRole($roleName);
                })->identite;
        } catch (\Exception $exception) {
            return null;
        }
    }

    protected function getStaffInstitutionMessageApi($institution)
    {
        try{
            return $institution->institutionType->name == 'membre'
                ? Institution::with('institutionMessageApi', 'institutionType')
                    ->get()
                    ->first(function ($value, $key) {
                        return $value->institutionType->name == 'observatory';
                    })->institutionMessageApi
                : $institution->institutionMessageApi;
        }catch (\Exception $exception){
            return null;
        }

    }

    protected function getUnitStaffIdentities($unitId)
    {
        return Staff::with('unit', 'identite.user')
            ->get()
            ->filter(function ($value, $key) use ($unitId) {

                if (is_null($value->unit) || is_null($value->identite)) {
                    return false;
                }

                if (is_null($value->identite->user)) {
                    return false;
                }

                return $value->unit->id == $unitId && $value->identite->user->hasRole('staff');
            })
            ->pluck('identite')
            ->values();
    }

    protected function getStaffIdentities($staffIds, $exceptIds = [])
    {
        return Staff::with('identite')
            ->get()
            ->filter(function ($value, $key) use ($staffIds, $exceptIds) {
                return is_null($value->identite)
                    ? false
                    : in_array($value->id, $staffIds) && !in_array($value->id, $exceptIds);
            })
            ->pluck('identite')
            ->values();
    }

    protected function getNotificationStatus($notificationType)
    {
        $data = [
            'RegisterAClaim' => ['incomplete', 'full'],
            'CompleteAClaim' => ['full'],
            'TransferredToTargetedInstitution' => ['transferred_to_targeted_institution'],
            'TransferredToUnit' => ['transferred_to_unit'],
            'RejectAClaim' => ['transferred_to_targeted_institution', 'full'],
            'AssignedToStaff' => ['assigned_to_staff'],
            'TreatAClaim' => ['treated'],
            'ValidateATreatment' => ['archived', 'validated'],
            'InvalidateATreatment' => ['assigned_to_staff'],
            'AddContributorToDiscussion' => ['assigned_to_staff', 'treated', 'validated'],
        ];

        return $data[$notificationType];
    }

}