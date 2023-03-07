<?php
namespace Satis2020\ServicePackage\Services\Monitoring;

use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;
use Satis2020\ServicePackage\Traits\CollectorTrait;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\StaffMonitoring;
use Satis2020\ServicePackage\Traits\Metadata;


class CollectorMonitoringService
{

    use Metadata,StaffMonitoring,ClaimSatisfactionMeasured,DataUserNature,CollectorTrait;

    public function MycollectorClaims($request)
    {
        $paginationSize = \request()->query('size');
        $type = \request()->query('type');
        $key = \request()->query('key');

        
         $claimSatisfied = $this->getClaimSatisfiedByCollector($request)->count();
         $claimUnSatisfied = $this->getClaimUnSatisfiedByCollector($request)->count();
         $claimWithMeasureOfSAtisfaction= $this->claimWithMeasureOfSAtisfaction($request)->count();
         $totalClaimSaved = $this->totalClaimSaved($request)->count();
         $claimReceivedForMeasure = $this->claimReceivedForMeasure($request)->count();
         $getAverageTimeOfSatisfaction = $this->getAverageTimeOfSatisfaction($request);
        
        
        $claimSaved = $this->getCollectorClaimSaved($request, $paginationSize, $type, $key);
        return [
            
            "getAverageTimeOfSatisfaction"=>  $getAverageTimeOfSatisfaction,
            "claimReceivedForMeasure"=>  $claimReceivedForMeasure,
            "claimSatisfied"=>  $claimSatisfied,
            "claimUnSatisfied"=>  $claimUnSatisfied,
            "claimWithMeasureOfSAtisfaction"=>  $claimWithMeasureOfSAtisfaction,
            "totalClaimSaved"=>  $totalClaimSaved,
            "claimSaved"=> $claimSaved,
        ];
    }
}
