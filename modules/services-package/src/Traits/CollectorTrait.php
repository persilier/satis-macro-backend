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
trait CollectorTrait
{


    protected function getClaimAssignedToUnit($request)
    {
        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
       ->whereNotNull('treatments.transferred_to_unit_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->unit_id != Constants::ALL_UNIT) {
        $claims->where('treatments.responsible_unit_id', $request->unit_id);
        }
       
        return $claims;
    }

   
    protected function getClaimSatisfiedByCollector($request)
    {
        

        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
        ->where('treatments.is_claimer_satisfied',true);

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->collector_id != Constants::ALL_COLLECTOR) {
        $claims->where('treatments.satisfaction_measured_by', $request->collector_id);
        }
       
        return $claims;
    }
    protected function getClaimUnSatisfiedByCollector($request)
    {
        
        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
        ->where('treatments.is_claimer_satisfied',false);

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->collector_id != Constants::ALL_COLLECTOR) {
        $claims->where('treatments.satisfaction_measured_by', $request->collector_id);
        }
       
        return $claims;
    }
    protected function claimWithMeasureOfSAtisfaction($request)
    {
        
        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
        ->whereNotNull('treatments.satisfaction_measured_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->collector_id != Constants::ALL_COLLECTOR) {
        $claims->where('treatments.satisfaction_measured_by', $request->collector_id);
        }
       
        return $claims;
    }
    protected function totalClaimSaved($request)
    {
        
        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
        ->whereNotNull('claims.created_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->collector_id != Constants::ALL_COLLECTOR) {
        $claims->where('claims.created_by', $request->collector_id);
        }
       
        return $claims;
    }
    protected function claimReceivedForMeasure($request)
    {
        
        $claims = Claim::query()->with($this->getRelations())
        ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
        ->whereNotNull('treatments.validated_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->collector_id != Constants::ALL_COLLECTOR) {
        $claims->where('treatments.validated_by', $request->collector_id);
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
           $totalTime +=  $value->timeLimitUnit['duration_done'];
        }
        
        $averageTime = $totalTime / $i;
       }
       

       return $averageTime;
    }
  
    /**
     * @param $request
     * @param int $paginationSize
     * @param null $type
     * @param null $key
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */

    protected function getCollectorClaimSaved($request,$paginationSize = 10, $type = null, $key = null)
    {

        $claims = Claim::query()->with($this->getRelations())
                        ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
                        ->whereNotNull('treatments.satisfaction_measured_by');

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
                
        if ($request->collector_id != Constants::ALL_COLLECTOR) {
            $claims->where('treatments.satisfaction_measured_by', $request->collector_id);
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
