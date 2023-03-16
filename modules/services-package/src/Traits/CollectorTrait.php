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

   
    protected function getClaimSatisfiedByCollector($request)
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

        if ($request->collector_id != Constants::ALL_COLLECTOR) {
        $claims->where('treatments.satisfaction_measured_by', $request->collector_id);
        }
       
        return $claims;
    }
    protected function getClaimUnSatisfiedByCollector($request)
    {
        
        $claims = Claim::query()->with($this->getRelations())
             ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
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
                ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
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
            ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
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
          ->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
            ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
        ->whereNotNull('treatments.validated_at');

        if ($request->has('institution_id')) {
        $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->collector_id != Constants::ALL_COLLECTOR) {
        $claims->where('treatments.validated_by', $request->collector_id);
        }
       
        return $claims;
    }


    protected function getAverageTimeOfSatisfaction($request)
    {
       $claimSatisfied =  $this->claimWithMeasureOfSAtisfaction($request);


       $i = 0;
       $totalTime = 0;
       if ($claimSatisfied->count() == 0) {
        $averageTime = 0;
       } else {

        $claimSatisfied =$claimSatisfied->get();
        foreach ($claimSatisfied as $value){
           $i++;
           $totalTime +=  $value->timeLimitMeasureSatisfaction['duration_done'];
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
                    ->select('claims.*')->join('treatments', function ($join) {
                        $join->on('claims.id', '=', 'treatments.claim_id')
                        ->on('claims.active_treatment_id', '=', 'treatments.id');
                    })
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
