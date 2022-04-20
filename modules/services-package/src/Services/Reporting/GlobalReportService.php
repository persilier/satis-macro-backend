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

    public function RecurringClaimsByClaimObject($request,$translateWord){

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
        return $dataRecurringClaimObject;
    }

    public function ClaimsReceivedByClaimCategory($request,$translateWord,$totalClaimsReceived){

        //claim received by category claim
        $totalReceivedClaimsByClaimCategory = $this->getClaimsReceivedByClaimCategory($request)->get();
        $dataReceivedClaimsByClaimCategory = [];
        foreach($totalReceivedClaimsByClaimCategory as $claimReceivedByClaimCategory){
            $percentage = $totalClaimsReceived!=0 ?number_format(($claimReceivedByClaimCategory->total / $totalClaimsReceived)*100,2):0;

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
        return $dataReceivedClaimsByClaimCategory;
    }

    public function ClaimsReceivedByClaimObject($request,$translateWord,$totalClaimsReceived){

        //claim received by object claim
        $claimReceivedByClaimObject = $this->getClaimsReceivedByClaimObject($request)->get();
        $dataClaimReceivedByClaimObject = [];
        foreach($claimReceivedByClaimObject as $receivedByClaimObject){
            $percentage = $totalClaimsReceived!=0 ?number_format(($receivedByClaimObject->total / $totalClaimsReceived)*100,2):0;

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

        return $dataClaimReceivedByClaimObject;
    }

    public function ClaimsReceivedByClientGender($request,$totalClaimsReceived){

        //claim received by gender
        $claimReceivedByClientGender = $this->getClaimsReceivedByClientGender($request)->get();
        $dataClaimReceivedByClientGender = [];
        foreach($claimReceivedByClientGender as $receivedByClientGender){
            $percentage = $totalClaimsReceived!=0 ?number_format(($receivedByClientGender->total / $totalClaimsReceived)*100,2):0;

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
        return $dataClaimReceivedByClientGender;
    }

    public function ClaimsResolvedOnTimeByUnit($request,$translateWord,$totalClaimsReceived){

        $claimsTreatedInTimeByUnit = $this->getClaimsResolvedOnTimeByUnit($request)->get();

        $dataClaimsTreatedInTimeByUnit = [];

        foreach($claimsTreatedInTimeByUnit as $treatedInTimeByUnit){

            $percentageOfClaimsTreatedInTimeByUnit  = $totalClaimsReceived!=0 ? number_format(($treatedInTimeByUnit->total / $totalClaimsReceived)*100,2):0;
            if($treatedInTimeByUnit->name==null){
                $treatedInTimeByUnit->name=$translateWord;
            }

            array_push(
                $dataClaimsTreatedInTimeByUnit,
                [
                    "Unit"=>json_decode($treatedInTimeByUnit->name),
                    "total"=>$treatedInTimeByUnit->total,
                    "percentage"=>$percentageOfClaimsTreatedInTimeByUnit
                ]
            );

        }
        return $dataClaimsTreatedInTimeByUnit;

    }

    public function RateOfClaimsSatisfactionByUnit($request,$translateWord,$totalClaimsReceived){

        $claimsSatisfactionByUnit = $this->getClaimsSatisfactionByUnit($request)->get();

        $dataClaimsSatisfactionByUnit = [];

        foreach($claimsSatisfactionByUnit as $satisfactionByUnit){

            $percentageOfClaimsSatisfactionByUnit  = $totalClaimsReceived!=0 ?number_format(($satisfactionByUnit->total / $totalClaimsReceived)*100,2):0;

            if($satisfactionByUnit->name==null){
                $satisfactionByUnit->name=$translateWord;
            }

            array_push(
                $dataClaimsSatisfactionByUnit,
                [
                    "Unit"=>json_decode($satisfactionByUnit->name),
                    "total"=>$satisfactionByUnit->total,
                    "percentage"=>$percentageOfClaimsSatisfactionByUnit
                ]
            );

        }
        return $dataClaimsSatisfactionByUnit;

    }

    public function RateOfHighlyClaimsTreatedInTimeByUnit($request,$translateWord,$totalClaimsReceived){

        $highlyClaimsTreatedInTimeByUnit = $this->getHighlyClaimsResolvedOnTimeByUnit($request)->get();
        $dataHighlyClaimsTreatedInTimeByUnit = [];

        foreach($highlyClaimsTreatedInTimeByUnit as $allHighlyClaimsTreatedInTimeByUnit){

            $percentageOfHighlyClaimsTreatedInTimeByUnit  = $totalClaimsReceived!=0 ?number_format(($allHighlyClaimsTreatedInTimeByUnit->total / $totalClaimsReceived)*100,2):0;

            if($allHighlyClaimsTreatedInTimeByUnit->name==null){
                $allHighlyClaimsTreatedInTimeByUnit->name=$translateWord;
            }

            array_push(
                $dataHighlyClaimsTreatedInTimeByUnit,
                [
                    "Unit"=>json_decode($allHighlyClaimsTreatedInTimeByUnit->name),
                    "total"=>$allHighlyClaimsTreatedInTimeByUnit->total,
                    "percentage"=>$percentageOfHighlyClaimsTreatedInTimeByUnit
                ]
            );

        }
        return $dataHighlyClaimsTreatedInTimeByUnit;

    }


    public function GlobalReport($request)
    {

        $translateWord = json_encode( [\app()->getLocale()=>"Autres"] );

        //claim received by period
        $totalClaimsReceived = $this->getAllClaimsByPeriod($request)->count();

        //rate of claims treated in time
        $claimsTreatedInTime = $this->getClaimsResolvedOnTime($request)->count();
        $percentageOfClaimsTreatedInTime  = $totalClaimsReceived!=0 ? number_format(($claimsTreatedInTime / $totalClaimsReceived)*100,2):0;

        //rate of claims treated in time by unit
        $claimsTreatedInTimeByUnit = $this->ClaimsResolvedOnTimeByUnit($request,$translateWord,$totalClaimsReceived);

        //claims satisfaction rate
        $claimsSatisfaction = $this->getClaimsSatisfaction($request)->count();
        $percentageOfClaimsSatisfaction  = $totalClaimsReceived!=0 ?number_format(($claimsSatisfaction / $totalClaimsReceived)*100,2):0;

        //claims satisfaction rate by unit
        $claimsSatisfactionByUnit = $this->RateOfClaimsSatisfactionByUnit($request,$translateWord,$totalClaimsReceived);

        //rate of treated highly claim in time
        $highlyClaimsTreatedInTime = $this->getHighlyClaimsResolvedOnTime($request)->count();
        $percentageOfHighlyClaimsTreatedInTime  = $totalClaimsReceived!=0 ?number_format(($highlyClaimsTreatedInTime / $totalClaimsReceived)*100,2):0;

        //rate of treated highly claim in time by unit
        $highlyClaimsTreatedInTimeByUnit = $this->RateOfHighlyClaimsTreatedInTimeByUnit($request,$translateWord,$totalClaimsReceived);

        //rate of treated low medium claim in time
        $lowMediumClaimsTreatedInTime = $this->getLowMediumClaimsResolvedOnTime($request)->count();
        $percentageOfLowMediumClaimsTreatedInTime = $totalClaimsReceived!=0 ? number_format(($lowMediumClaimsTreatedInTime / $totalClaimsReceived)*100,2):0;

        //claim received resolved
        $totalClaimsResolved = $this->getClaimsResolved($request)->count();

        //claim received unresolved
        $totalClaimsUnresolved = $this->getClaimsUnresolved($request)->count();

        //claim received resolved on time
        $totalClaimResolvedOnTime = $this->getClaimsResolvedOnTime($request)->count();

        //claim received resolved Late
        $totalClaimResolvedLate = $this->getClaimsResolvedLate($request)->count();

        //3 recurrent object claim
        $recurringClaimObject = $this->RecurringClaimsByClaimObject($request,$translateWord);

        //claim received by category claim
        $totalReceivedClaimsByClaimCategory = $this->ClaimsReceivedByClaimCategory($request,$translateWord,$totalClaimsReceived);

        //claim received by object claim
        $claimReceivedByClaimObject = $this->ClaimsReceivedByClaimObject($request,$translateWord,$totalClaimsReceived);

        //claim received by gender
        $claimReceivedByClientGender = $this->ClaimsReceivedByClientGender($request,$totalClaimsReceived);

        //total of client contacted after treatment
        $clientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request)->count();
        //total of client dissatisfied and percentage of satisfied client
        $claimOfClientDissatisfied = $this->getClaimsDissatisfied($request)->count();
        $percentageOfClientDissatisfied = $clientContactedAfterTreatment!=0 ?number_format( ($claimOfClientDissatisfied / $clientContactedAfterTreatment)*100,2):0;
        $percentageOfClientSatisfied = $clientContactedAfterTreatment!=0 ?number_format( ($claimsSatisfaction / $clientContactedAfterTreatment)*100,2):0;


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
            'RecurringClaimsByClaimObject'=>$recurringClaimObject,

            'ClaimsReceivedByClaimCategory'=>$totalReceivedClaimsByClaimCategory,
            'ClaimsReceivedByClaimObject'=>$claimReceivedByClaimObject,
            'ClaimsReceivedByClientGender'=>$claimReceivedByClientGender,

            'ClientContactedAfterTreatment'=>$clientContactedAfterTreatment,
            'NumberOfClientSatisfied'=>$claimsSatisfaction,
            'PercentageOfClientSatisfied'=>$percentageOfClientSatisfied,
            'NumberOfClientDissatisfied'=>$claimOfClientDissatisfied,
            'PercentageOfClientDissatisfied'=>$percentageOfClientDissatisfied,

            'RateOfClaimsTreatedInTimeByUnit'=>$claimsTreatedInTimeByUnit,
            'RateOfClaimsSatisfactionByUnit'=>$claimsSatisfactionByUnit,
            'RateOfHighlyClaimsTreatedInTimeByUnit'=>$highlyClaimsTreatedInTimeByUnit,
        ];
    }


}
