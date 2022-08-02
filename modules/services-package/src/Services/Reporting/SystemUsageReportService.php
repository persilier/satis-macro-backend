<?php

namespace Satis2020\ServicePackage\Services\Reporting;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;
use Satis2020\ServicePackage\Traits\Metadata;

class SystemUsageReportService
{

    use Metadata,FilterClaims,DataUserNature;

    public function SystemUsageReport($request)
    {

        $totalReceivedClaims = $this->getAllClaimsByPeriod($request)->count();
        $totalTreatedClaims = $this->getClaimsTreatedByPeriod($request,Claim::CLAIM_VALIDATED)->count();
        $totalSatisfactionMeasured = $this->getClaimsSatisfactionMeasured($request,Claim::CLAIM_VALIDATED)->count();

        return [
            'title' => $this->getMetadataByName(Constants::SYSTEM_USAGE_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::SYSTEM_USAGE_REPORTING)->description,

            'totalReceivedClaims'=>$totalReceivedClaims,
            'totalTreatedClaims'=>$totalTreatedClaims,
            'totalSatisfactionMeasured'=>$totalSatisfactionMeasured
        ];
    }


}
