<?php
namespace Satis2020\ServicePackage\Services\Monitoring;

use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\StaffMonitoring;
use Satis2020\ServicePackage\Traits\PilotMonitoringTrait;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;


class PilotMonitoringService
{

    use Metadata,StaffMonitoring,ClaimSatisfactionMeasured,DataUserNature,PilotMonitoringTrait;

    public function MyPilotMonitoring($request)
    {
        $paginationSize = \request()->query('size');
        $type = \request()->query('type');
        $key = \request()->query('key');

        
         $totalClaimAssigned = $this->getClaimAssigned($request)->count();
         $totalClaimValidated = $this->getClaimValidated($request)->count();
         $totalClaimSatisfied = $this->getClaimSatisfied($request)->count();
       
         $allClaim = $this->getPilotClaimAssignedTo($request, $paginationSize, $type, $key);

        
        return [
            'totalClaimAssigned' => $totalClaimAssigned,
            'totalClaimValidated' => $totalClaimValidated,
            'totalClaimSatisfied' => $totalClaimSatisfied,
            'allClaimAssignedTo' => $allClaim
        ];
   
    }
}
