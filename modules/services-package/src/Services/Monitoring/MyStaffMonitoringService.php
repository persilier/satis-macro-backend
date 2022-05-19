<?php

namespace Satis2020\ServicePackage\Services\Monitoring;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\StaffMonitoring;
use Satis2020\ServicePackage\Traits\Metadata;


class MyStaffMonitoringService
{

    use Metadata,StaffMonitoring,ClaimSatisfactionMeasured,DataUserNature;

    public function MyStaffMonitoring($request,$unitId)
    {
        $thisDay = date("Y-m-d");
        $paginationSize = \request()->query('size');
        $recherche = \request()->query('key');

        $claimAssigned = $this->getClaimAssigned($request,$unitId,$thisDay)->count();
        $claimTreated = $this->getClaimTreated($request,$unitId,$thisDay)->count();
        $claimNoTreated = $claimAssigned - $claimTreated;

        $staffClaims = $this->getAllStaffClaim($request, $unitId, $thisDay, true,$paginationSize, $recherche)->get();

        return [
            "claimAssignedToStaff"=>$claimAssigned,
            "claimTreatedByStaff"=>$claimTreated,
            "claimNoTreatedByStaff"=>$claimNoTreated,
            "allStaffClaim"=>$staffClaims,
        ];

    }


}
