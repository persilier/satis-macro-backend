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

        if($totalClaimsReceived!=0){
            $rateOfClaimsTreatedInTime  = ($claimsTreatedInTime / $totalClaimsReceived)*100;
            $percentageOfClaimsTreatedInTime = number_format((float)$rateOfClaimsTreatedInTime);

        }else{
            $rateOfClaimsTreatedInTime  = 0;
            $percentageOfClaimsTreatedInTime = 0;
        }


        //claims satisfaction rate
        $claimsSatisfaction = $this->getClaimsSatisfaction($request)->count();
        if($totalClaimsReceived!=0){
            $rateOfClaimsSatisfaction  = ($claimsSatisfaction / $totalClaimsReceived)*100;
            $percentageOfClaimsSatisfaction = number_format((float)$rateOfClaimsSatisfaction);
        }else{
            $rateOfClaimsSatisfaction  = 0;
            $percentageOfClaimsSatisfaction = 0;
        }

        //rate of treated highly claim in time
        $highlyClaimsTreatedInTime = $this->getHighlyClaimsResolvedOnTime($request)->count();
        if($totalClaimsReceived!=0){
            $rateHighlyClaimsTreatedInTime  = ($highlyClaimsTreatedInTime / $totalClaimsReceived)*100;
            $percentageOfHighlyClaimsTreatedInTime = number_format((float)$rateHighlyClaimsTreatedInTime);
        }else{
            $rateHighlyClaimsTreatedInTime  = 0;
            $percentageOfHighlyClaimsTreatedInTime = 0;
        }

        //rate of treated low medium claim in time
        $lowMediumClaimsTreatedInTime = $this->getLowMediumClaimsResolvedOnTime($request)->count();
        if($totalClaimsReceived!=0){
            $rateLowMediumClaimsTreatedInTime  = ($lowMediumClaimsTreatedInTime / $totalClaimsReceived)*100;
            $percentageOfLowMediumClaimsTreatedInTime = number_format((float)$rateLowMediumClaimsTreatedInTime);
        }else{
            $rateLowMediumClaimsTreatedInTime  = 0;
            $percentageOfLowMediumClaimsTreatedInTime = 0;
        }

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

        foreach($recurringClaimObject as $key => $threeRecurringClaimObject ){

            if($threeRecurringClaimObject->name==null){
                $threeRecurringClaimObject->name=$translateWord;
            }

                array_push(
                    $dataRecurringClaimObject,
                    [
                        "ClaimsObject"=>json_decode($threeRecurringClaimObject->name),
                        "total"=>$threeRecurringClaimObject->total,
                        "rank"=>$key+1
                    ]
                );

        }


        //claim received by category claim
        $totalReceivedClaimsByClaimCategory = $this->getClaimsReceivedByClaimCategory($request)->get();
        $dataReceivedClaimsByClaimCategory = [];
        foreach($totalReceivedClaimsByClaimCategory as $claimReceivedByClaimCategory){

            if($totalClaimsReceived!=0){
                $result = ($claimReceivedByClaimCategory->total / $totalClaimsReceived)*100;
                $percentage = number_format((float)$result);
            }else{
                $result  = 0;
                $percentage = 0;
            }

            if($claimReceivedByClaimCategory->name==null){
                $claimReceivedByClaimCategory->name=$translateWord;
            }

            array_push(
                $dataReceivedClaimsByClaimCategory,
                [
                    "CategoryClaims"=>json_decode($claimReceivedByClaimCategory->name),
                    "total"=>$claimReceivedByClaimCategory->total,
                    "percentage"=>$percentage
                ]
            );

        }

        //claim received by object claim
        $claimReceivedByClaimObject = $this->getClaimsReceivedByClaimObject($request)->get();
        $dataClaimReceivedByClaimObject = [];
        foreach($claimReceivedByClaimObject as $receivedByClaimObject){

            if($totalClaimsReceived!=0){
                $result = ($receivedByClaimObject->total / $totalClaimsReceived)*100;
                $percentage = number_format((float)$result);
            }else{
                $result  = 0;
                $percentage = 0;
            }

            if($receivedByClaimObject->name==null){
                $receivedByClaimObject->name=$translateWord;
            }

            array_push(
                $dataClaimReceivedByClaimObject,
                [
                    "ClaimsObject"=>json_decode($receivedByClaimObject->name),
                    "total"=>$receivedByClaimObject->total,
                    "percentage"=>$percentage
                ]
            );

        }


        //claim received by object claim
        $claimReceivedByClientGender = $this->getClaimsReceivedByClientGender($request)->get();
        $dataClaimReceivedByClientGender = [];
        foreach($claimReceivedByClientGender as $receivedByClientGender){

            if($totalClaimsReceived!=0){
                $result = ($receivedByClientGender->total / $totalClaimsReceived)*100;
                $percentage = number_format((float)$result);
            }else{
                $result  = 0;
                $percentage = 0;
            }

            if($receivedByClientGender->sexe==null){
                $receivedByClientGender->sexe="Autres";
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

        //total of client contacted after treatment
        $clientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request)->count();

        //total of client dissatisfied and percentage of satisfied client
        $claimOfClientDissatisfied = $this->getClaimsDissatisfied($request)->count();
        if($clientContactedAfterTreatment!=0){
            $rateOfClientDissatisfied = ($claimOfClientDissatisfied / $clientContactedAfterTreatment)*100;
            $rateOfClientSatisfied = ($claimsSatisfaction / $clientContactedAfterTreatment)*100;

            $percentageOfClientDissatisfied = number_format((float)$rateOfClientDissatisfied);
            $percentageOfClientSatisfied = number_format((float)$rateOfClientSatisfied);
        }else{
            $percentageOfClientDissatisfied = 0;
            $percentageOfClientSatisfied = 0;
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
            'ClaimsReceivedByClaimGender'=>$dataClaimReceivedByClientGender,

            'ClientContactedAfterTreatment'=>$clientContactedAfterTreatment,
            'RateOfClientSatisfied'=>$claimsSatisfaction.'==>'.$percentageOfClientSatisfied,
            'RateOfClientDissatisfied'=>$claimOfClientDissatisfied.'==>'.$percentageOfClientDissatisfied,
        ];
    }


}
