<?php

namespace Satis2020\ServicePackage\Services\Reporting;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;

class SystemEfficiencyReportService
{

    use FilterClaims,DataUserNature;

    public function getReportData($request)
    {
        $relations = [
            'claimObject.claimCategory','activeTreatment'
        ];

        $totalUntreatedClaims = $this->getUnTreatedClaims($this->getAllClaimsByPeriod($request,$relations))->count();
        $totalTreatedClaimsInTime = $this->getTreatedInTimeClaims($request,$relations)->count();
        $totalTreatedClaimsOutOfTime =$this->getTreatedOutOfTimeClaims($request,$relations)->count();
        $totalRevivalClaims =$this->getRevivalClaims($this->getAllClaimsByPeriod($request,$relations))->count();
        $rateOfSatisfaction =$this->getSatisfactionRate($request,$relations);
        $averageNumberOfDaysForTreatment = $this->getAverageNumberOfDaysForTreatment($request,$relations);

        return [
            'totalUntreatedClaims'=>$totalUntreatedClaims,
            'totalTreatedClaimsInTime'=>$totalTreatedClaimsInTime,
            'totalTreatedClaimsOutOfTime'=>$totalTreatedClaimsOutOfTime,
            'totalRevivalClaims'=>$totalRevivalClaims,
            'rateOfSatisfaction'=>$rateOfSatisfaction,
            'averageNumberOfDaysForTreatment'=>$averageNumberOfDaysForTreatment
        ];
    }



}
