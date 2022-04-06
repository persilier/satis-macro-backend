<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\InactivityReactivationHistory;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\UserPackage\Http\Resources\User;

trait CheckInactivityDuration
{
    public function inactivityTimeIsPassed($user,$configs)
    {

        $activityLogService = app(ActivityLogService::class);
        $lastLog = $activityLogService
            ->getLastLogByUserAndAction(
                 $user->id,
                ActivityLogService::LOGOUT);

            if ($lastLog!=null){
                $desactivationDate = Carbon::parse($lastLog->created_at)->addDays($configs->inactivity_time_limit);
                return Carbon::parse(now()) > $desactivationDate;
            }else{
                return false;
            }

        return true;
    }

    /*
     * This method checks if the user has been inactive after reactivation of his account
     */
    public function inactivityTimeIsPassedAfReactivation($user,$configs)
    {

        $lastReactivationDate = InactivityReactivationHistory::where('user_id', $user->id)->where('action',InactivityReactivationHistory::ACTIVATION)->orderByDesc('created_at')->first();;

        if ($lastReactivationDate!=null){
            $nextDeactivationDate = Carbon::parse($lastReactivationDate->created_at)->addDays($configs->inactivity_time_limit);
            return Carbon::parse(now()) > $nextDeactivationDate;
        }

        return false;
    }


    public function accountHasBeenReactivated($user)
    {
        $lastReactivation = InactivityReactivationHistory::query()
            ->where('user_id',$user->id)
            ->where('action',InactivityReactivationHistory::ACTIVATION)
            ->latest()
            ->first();
        $lastDeactivation = InactivityReactivationHistory::query()
            ->where('user_id',$user->id)
            ->where('action',InactivityReactivationHistory::DEACTIVATION)
            ->latest()
            ->first();

        if ($lastDeactivation!=null && $lastReactivation!=null ){
            $deactivationDate = Carbon::parse($lastDeactivation->created_at);
            $reactivationDate = Carbon::parse($lastReactivation->created_at);

            return $reactivationDate->gt($deactivationDate);
        }



        return false;
    }

}