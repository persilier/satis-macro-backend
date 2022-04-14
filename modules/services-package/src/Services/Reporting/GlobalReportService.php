<?php

namespace Satis2020\ServicePackage\Services\Reporting;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;

class GlobalReportService
{

    use FilterClaims,DataUserNature;

    public function GlobalReport($request)
    {

        $translateWord = json_encode( [\app()->getLocale()=>"Autres"] );

        //claim received by period
        $totalClaimsReceived = $this->getAllClaimsByPeriod($request)->count();

        //rate of claims treated in time
        $claimsTreatedInTime = $this->getClaimsResolvedOnTime($request)->count();
        $rateOfClaimsTreatedInTime  = ($claimsTreatedInTime / $totalClaimsReceived)*100;
        $percentageOfClaimsTreatedInTime = number_format((float)$rateOfClaimsTreatedInTime,2, '.', '');

        //claims satisfaction rate
        $claimsSatisfaction = $this->getClaimsSatisfaction($request)->count();
        $rateOfClaimsSatisfaction  = ($claimsSatisfaction / $totalClaimsReceived)*100;
        $percentageOfClaimsSatisfaction = number_format((float)$rateOfClaimsSatisfaction,2, '.', '');

        //rate of treated highly claim in time
        $highlyClaimsTreatedInTime = $this->getHighlyClaimsResolvedOnTime($request)->count();
        $rateHighlyClaimsTreatedInTime  = ($highlyClaimsTreatedInTime / $totalClaimsReceived)*100;
        $percentageOfHighlyClaimsTreatedInTime = number_format((float)$rateHighlyClaimsTreatedInTime,2, '.', '');

        //rate of treated low medium claim in time
        $lowMediumClaimsTreatedInTime = $this->getLowMediumClaimsResolvedOnTime($request)->count();
        $rateLowMediumClaimsTreatedInTime  = ($lowMediumClaimsTreatedInTime / $totalClaimsReceived)*100;
        $percentageOfLowMediumClaimsTreatedInTime = number_format((float)$rateLowMediumClaimsTreatedInTime,2, '.', '');

        //claim received resolved
        $totalClaimsResolved = $this->getClaimsResolved($request)->count();

        //claim received unresolved
        $totalClaimsUnresolved = $this->getClaimsUnresolved($request)->count();

        //claim received resolved on time
        $totalClaimResolvedOnTime = $this->getClaimsResolvedOnTime($request)->count();

        //claim received resolved Late
        $totalClaimResolvedLate = $this->getClaimsResolvedLate($request)->count();

        //3 recurrent object claim
        $recurringClaimObject = $this->getClaimsReceivedByClaimObject($request)->limit(3)->get();
        $dataRecurringClaimObject = [];
        foreach($recurringClaimObject as $threeRecurringClaimObject){

            if($threeRecurringClaimObject->name==null){
                $threeRecurringClaimObject->name=$translateWord;
            }

            array_push(
                $dataRecurringClaimObject,
                [
                    "ClaimsObject"=>$threeRecurringClaimObject->name,
                    "total"=>$threeRecurringClaimObject->total
                ]
            );

        }


        //claim received by category claim
        $totalReceivedClaimsByClaimCategory = $this->getClaimsReceivedByClaimCategory($request)->get();
        $dataReceivedClaimsByClaimCategory = [];
        foreach($totalReceivedClaimsByClaimCategory as $claimReceivedByClaimCategory){

            $result = ($claimReceivedByClaimCategory->total / $totalClaimsReceived)*100;
            $percentage = number_format((float)$result,2, '.', '');

            if($claimReceivedByClaimCategory->name==null){
                $claimReceivedByClaimCategory->name=$translateWord;
            }

            array_push(
                $dataReceivedClaimsByClaimCategory,
                [
                    "CategoryClaims"=>$claimReceivedByClaimCategory->name,
                    "total"=>$claimReceivedByClaimCategory->total,
                    "percentage"=>$percentage
                ]
            );

        }

        //claim received by object claim
        $claimReceivedByClaimObject = $this->getClaimsReceivedByClaimObject($request)->get();
        $dataClaimReceivedByClaimObject = [];
        foreach($claimReceivedByClaimObject as $receivedByClaimObject){

            $result = ($receivedByClaimObject->total / $totalClaimsReceived)*100;
            $percentage = number_format((float)$result,2, '.', '');


            if($receivedByClaimObject->name==null){
                $receivedByClaimObject->name=$translateWord;
            }

            array_push(
                $dataClaimReceivedByClaimObject,
                [
                    "ClaimsObject"=>$receivedByClaimObject->name,
                    "total"=>$receivedByClaimObject->total,
                    "percentage"=>$percentage
                ]
            );

        }


        //claim received by object claim
        $claimReceivedByClientGender = $this->getClaimsReceivedByClientGender($request)->get();
        $dataClaimReceivedByClientGender = [];
        foreach($claimReceivedByClientGender as $receivedByClientGender){

            $result = ($receivedByClientGender->total / $totalClaimsReceived)*100;
            $percentage = number_format((float)$result,2, '.', '');

            if($receivedByClientGender->sexe==null){
                $receivedByClientGender->sexe=$translateWord;
            }

            array_push(
                $dataClaimReceivedByClientGender,
                [
                    "ClientGender"=>$receivedByClientGender->sexe,
                    "total"=>$receivedByClientGender->total,
                    "percentage"=>$percentage
                ]
            );

        }


        return [
            'RateOfClaimsTreatedInTime'=>$percentageOfClaimsTreatedInTime,
            'RateOfClaimsSatisfaction'=>$percentageOfClaimsSatisfaction,
            'RateOfHighlyClaimsTreatedInTime'=>$percentageOfHighlyClaimsTreatedInTime,

            'TotalClaimsReceived'=>$totalClaimsReceived,
            'TotalClaimsResolved'=>$totalClaimsResolved,
            'TotalClaimsUnresolved'=>$totalClaimsUnresolved,
            'TotalClaimResolvedOnTime'=>$totalClaimResolvedOnTime,
            'TotalClaimResolvedLate'=>$totalClaimResolvedLate,
            'RateOLowMediumClaimsTreatedInTime'=>$percentageOfLowMediumClaimsTreatedInTime,
            'RecurringClaimsByClaimObject'=>$dataRecurringClaimObject,

            'ClaimsReceivedByClaimCategory'=>$dataReceivedClaimsByClaimCategory,
            'ClaimsReceivedByClaimObject'=>$dataClaimReceivedByClaimObject,
            'ClaimsReceivedByClaimGender'=>$dataClaimReceivedByClientGender
        ];
    }


}
