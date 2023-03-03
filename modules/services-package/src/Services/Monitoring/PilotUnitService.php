<?php
namespace Satis2020\ServicePackage\Services\Monitoring;

use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\PilotUnitTrait;
use Satis2020\ServicePackage\Traits\StaffMonitoring;
use Satis2020\ServicePackage\Traits\PilotMonitoringTrait;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;


class PilotUnitService
{

    use Metadata,ClaimSatisfactionMeasured,DataUserNature,PilotUnitTrait;

    public function PilotUnitMonitoring($request)
    {
        $paginationSize = \request()->query('size');
        $type = \request()->query('type');
        $key = \request()->query('key');

        
        $totalClaimAssigned = $this->getClaimAssignedToUnit($request)->count();
        $totalClaimTreated= $this->getClaimTreatedByUnit($request)->count();
        $totalClaimNotTreated= $this->getClaimNotTreatedByUnit($request)->count();
        $totalClaimSatisfied = $this->getClaimSatisfiedByUnit($request)->count();
        $getAverageTimeOfAssignation = $this->getAverageTimeOfAssignation($request);
 
       
         $allClaim = $this->getPilotClaimAssignedToUnit($request, $paginationSize, $type, $key);

        
        return [
            'totalClaimAssigned' => $totalClaimAssigned,
            'totalClaimTreated' => $totalClaimTreated,
            'totalClaimNotTreated' => $totalClaimNotTreated,
            'totalClaimSatisfied' => $totalClaimSatisfied,
            'getAverageTimeOfAssignation'  =>  $getAverageTimeOfAssignation,
            'allClaimAssignedTo' => $allClaim
        ];
   
    }

    
}
