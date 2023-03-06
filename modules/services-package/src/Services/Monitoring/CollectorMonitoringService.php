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

        
        // $claimSatisfied = $this->getClaimSatisfied($request,$unitId)->count();
        // $claimAssigned = $this->getClaimAssigned($request,$unitId)->count();
        // $claimTreated = $this->getClaimTreated($request,$unitId)->count();
        // $claimNoTreated = $this->getClaimNoTreated($request,$unitId)->count();
        // $getAverageTimeOfTreatment = $this->getAverageTimeOfTreatment($request,$unitId);

        
        
        $claimSaved = $this->getCollectorClaimSaved($request, $paginationSize, $type, $key);
        return [
            
            "claimSaved"=> $claimSaved,
        ];
    }
}
