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
trait PilotUnitTrait
{


    protected function getClaimAssignedToUnit($request)
    {
        $claims = Claim::query()->with($this->getRelations())
         ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
       ->whereNotNull('treatments.transferred_to_unit_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->unit_id != Constants::ALL_UNIT) {
        $claims->where('treatments.responsible_unit_id', $request->unit_id);
        }
       
        return $claims;
    }

    protected function getClaimTreatedByUnit($request)
    {
        $claims = Claim::query()->with($this->getRelations())
         ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
       ->whereNotNull('treatments.transferred_to_unit_at')
       ->whereNotNull('treatments.solved_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->unit_id != Constants::ALL_UNIT) {
        $claims->where('treatments.responsible_unit_id', $request->unit_id);
        }
       
        return $claims;
    }
    protected function getClaimNotTreatedByUnit($request)
    {
        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
       ->whereNotNull('treatments.transferred_to_unit_at')
       ->whereNull('treatments.solved_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->unit_id != Constants::ALL_UNIT) {
        $claims->where('treatments.responsible_unit_id', $request->unit_id);
        }
       
        return $claims;
    }
    protected function getClaimSatisfiedByUnit($request)
    {
        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
       ->where('treatments.is_claimer_satisfied',true);
      

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->unit_id != Constants::ALL_UNIT) {
        $claims->where('treatments.responsible_unit_id', $request->unit_id);
        }
       
        return $claims;
    }


     /**
     * @param $request
     * @return Builder
     */
    protected function getAverageTimeOfAssignation($request)
    {
       $claimAssigned = $this->getClaimAssignedToUnit($request);
    
       $i = 0;
       $totalTime = 0;
       if ($claimAssigned->count() == 0) {
        $averageTime = 0;
       } else {
        $claimAssigned = $claimAssigned->get();
        foreach ($claimAssigned as $value){
        
           $i++;
           $totalTime +=  $value->timeLimitUnit['time_done_in_minutes'];
        }
        
        $averageTime = $totalTime / $i;
       }
       

       return conversionToDayHourMinute($averageTime);
    }
  
    /**
     * @param $request
     * @param int $paginationSize
     * @param null $type
     * @param null $key
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */

    protected function getPilotClaimAssignedToUnit($request,$paginationSize = 10, $type = null, $key = null)
    {
     

        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
                       ->whereNotNull('treatments.transferred_to_unit_at');

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
                
        if ($request->unit_id != Constants::ALL_UNIT) {
            $claims->where('treatments.responsible_unit_id', $request->unit_id);
        }

        if ($request->status) {

           if ($request->status == "assigned") {
            
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
