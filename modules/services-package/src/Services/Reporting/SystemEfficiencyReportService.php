<?php

namespace Satis2020\ServicePackage\Services\Reporting;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;
use Satis2020\ServicePackage\Traits\Metadata;

class SystemEfficiencyReportService
{

    use FilterClaims,DataUserNature,Metadata;

    public function getReportData($request)
    {
        $relations = [
            'claimObject.claimCategory','activeTreatment'
        ];

        $totalUntreatedClaims = $this->getUnTreatedCustomClaims($this->getAllClaimsByPeriod($request,$relations))->count();
        $totalTreatedClaimsInTime = $this->getTreatedInTimeClaims($request,$relations)->count();
        $totalTreatedClaimsOutOfTime =$this->getTreatedOutOfTimeClaims($request,$relations)->count();
        $totalRevivalClaims =$this->getRevivalClaims($this->getAllClaimsByPeriod($request,$relations))->count();
        $rateOfSatisfaction =$this->getSatisfactionRate($request,$relations);
        $rateOfNotSatisfaction =$this->getNotSatisfactionRate($request,$relations);
        $averageNumberOfDaysForTreatment = $this->getAverageNumberOfDaysForTreatment($request,$relations);

        return [
            'title' => $this->getMetadataByName(Constants::SYSTEM_EFFICIENCY_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::SYSTEM_EFFICIENCY_REPORTING)->description,
            'totalUntreatedClaims'=>$totalUntreatedClaims,
            'totalTreatedClaimsInTime'=>$totalTreatedClaimsInTime,
            'totalTreatedClaimsOutOfTime'=>$totalTreatedClaimsOutOfTime,
            'totalRevivalClaims'=>$totalRevivalClaims,
            'rateOfSatisfaction'=>$rateOfSatisfaction,
            'rateOfNotSatisfaction'=>$rateOfNotSatisfaction,
            'averageNumberOfDaysForTreatment'=>$averageNumberOfDaysForTreatment
        ];
    }



}
