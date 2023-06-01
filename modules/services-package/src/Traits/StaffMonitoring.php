<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;


/**
 * Trait ReportingClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait StaffMonitoring
{

    /**
     * @param $request
     * @param $unitId
     * @return Builder
     */

    protected function getClaimAssigned($request, $unitId)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->where('responsible_unit_id', $unitId);
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        $claims = $claims->whereNotNull('treatments.assigned_to_staff_at')
            ->whereNull('claims.deleted_at');
        return $claims;
    }


    /**
     * @param $request
     * @param $unitId
     * @return Builder
     */
    protected function getClaimTreated($request, $unitId)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->where('responsible_unit_id', $unitId);
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        $claims = $claims->whereNotNull('treatments.assigned_to_staff_at')
            ->whereNotNull('treatments.solved_at')
            ->whereNull('claims.deleted_at');
        return $claims;
    }



    /**
     * @param $request
     * @param $unitId
     * @return Builder
     */
    protected function getClaimNoTreated($request, $unitId)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->where('responsible_unit_id', $unitId);
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        $claims = $claims->whereNotNull('treatments.transferred_to_unit_at')
            ->whereNotNull('treatments.assigned_to_staff_at')
            ->whereNull('treatments.solved_at')
            ->whereNull('claims.deleted_at');
        return $claims;
    }


    /**
     * @param $request
     * @param $unitId
     * @return Builder
     */
    protected function getClaimSatisfied($request, $unitId)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->where('responsible_unit_id', $unitId);
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        $claims = $claims->where('treatments.is_claimer_satisfied', true);
        return $claims;
    }
    
     /**
     * @param $request
     * @param $unitId
     * @return Builder
     */
    protected function getAverageTimeOfTreatment($request, $unitId)
    {
       $claimTreated = $this->getClaimTreated($request, $unitId);

       $i = 0;
       $totalTime = 0;
       if ($claimTreated->count() == 0) {
        $averageTime = 0;
       } else {
        $claimTreated = $claimTreated->get();
        foreach ($claimTreated as $value){
        
           $i++;
           $totalTime +=  $value->timeLimitUnit['time_done_in_minutes'];
        }
        
        $averageTime = $totalTime / $i;
       }


       return conversionToDayHourMinute($averageTime);
    }


    /**
     * @param $request
     * @param $unitId
     * @param int $paginationSize
     * @param null $type
     * @param null $key
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */

    protected function getAllStaffClaim($request, $unitId, $paginationSize = 10, $type = null, $key = null)
    {

        $claims =  Claim::query()->with($this->getRelations())
            ->join('treatments', function ($join) {
                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id')
                    ->whereNotNull('treatments.transferred_to_unit_at');
            })->where('responsible_unit_id', $unitId)
            ->whereNotNull('treatments.assigned_to_staff_at')
            ->whereNull('claims.deleted_at');
        if ($request->has('institution_id')) {
            $claims = $claims->where('institution_targeted_id', $request->institution_id);
        }
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims = $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        if ($request->status) {

           if ($request->status == "received") {
            
             $claims = $claims;
           }
           if ($request->status == "treated") {
            
             $claims = $claims->whereNotNull('treatments.solved_at');
           }
           if ($request->status == "not_treated") {
            
             $claims = $claims->whereNull('treatments.solved_at');
           }
        }

        if ($key) {
            switch ($key) {
                case 'reference':
                    $claims = $claims->where('reference', 'LIKE', "%$key%");
                    break;
                case 'claimObject':
                    $claims = $claims->whereHas("claimObject", function ($query) use ($key) {
                        $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    });
                    break;
                default:
                    $claims = $claims->whereHas("claimer", function ($query) use ($key) {
                        $query->where('firstname', 'like', "%$key%")
                            ->orWhere('lastname', 'like', "%$key%")
                            ->orwhereJsonContains('telephone', $key)
                            ->orwhereJsonContains('email', $key);
                    });
                    break;
            }
        }

        return $claims->paginate($paginationSize);
    }
}
