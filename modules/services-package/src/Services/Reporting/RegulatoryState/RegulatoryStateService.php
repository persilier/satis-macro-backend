<?php

namespace Satis2020\ServicePackage\Services\Reporting\RegulatoryState;


use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;
use Satis2020\ServicePackage\Traits\UemoaReports;

class RegulatoryStateService
{
    use FilterClaims,DataUserNature,UemoaReports;

    public function generateReport($request)
    {
        $relations = [
            'claimObject.claimCategory', 'unitTargeted',
        ];
        $receivedClaims = $this->getAllClaimsByPeriod($request,$relations)->get();
        $treatedClaims = $this->getClaimsByStatus($request,$relations,Claim::CLAIM_VALIDATED)->get();
        $unresolvedClaims = $this->getAllClaimsByPeriod($request,$relations)
            ->whereNotIn("id",$this->getClaimsByStatus($request,Claim::CLAIM_VALIDATED)
                ->pluck("id")->toArray())->get();

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        return [
            'receivedClaims'=>$receivedClaims,
            'treatedClaims'=>$treatedClaims,
            'institution'=>$this->institution(),
            'number_of_claims_litigated_in_court'=>$request->number_of_claims_litigated_in_court,
            'total_amount_of_claims_litigated_in_court'=>$request->total_amount_of_claims_litigated_in_court,
            'unresolvedClaims'=>$unresolvedClaims,
            'libellePeriode'=>$libellePeriode
        ];
    }
}