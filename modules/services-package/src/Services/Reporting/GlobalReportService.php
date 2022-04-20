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

    public function ClaimsResolvedOnTime($request,$translateWord,$totalClaimsReceived){

        if ($request->has('unit_targeted_id')) {

            $claimsTreatedInTimeByUnit = $this->getClaimsResolvedOnTime($request);
            $dataClaimsTreatedInTime = [];

            foreach($claimsTreatedInTimeByUnit as $treatedInTimeByUnit){

                $percentageOfClaimsTreatedInTimeByUnit  = $totalClaimsReceived!=0 ? number_format(($treatedInTimeByUnit->total / $totalClaimsReceived)*100,2):0;
                if($treatedInTimeByUnit->name==null){
                    $treatedInTimeByUnit->name=$translateWord;
                }

                array_push(
                    $dataClaimsTreatedInTime,
                    [
                        "Unit"=>json_decode($treatedInTimeByUnit->name),
                        "total"=>$treatedInTimeByUnit->total,
                        "percentage"=>$percentageOfClaimsTreatedInTimeByUnit
                    ]
                );

            }

        }else{

            //rate of claims treated in time
            $claimsTreatedInTime = $this->getClaimsResolvedOnTime($request);
            $dataClaimsTreatedInTime  = $totalClaimsReceived!=0 ? number_format(($claimsTreatedInTime / $totalClaimsReceived)*100,2):0;
            $dataClaimsTreatedInTime=[
                'total'=>$claimsTreatedInTime,
                'taux'=>$dataClaimsTreatedInTime,
            ];

        }

        return $dataClaimsTreatedInTime;

    }

    public function ClaimsSatisfaction($request,$translateWord,$totalClaimsReceived){

        if ($request->has('unit_targeted_id')) {

            $claimsSatisfactionByUnit = $this->getClaimsSatisfaction($request);
            $dataClaimsSatisfaction = [];

            foreach ($claimsSatisfactionByUnit as $satisfactionByUnit) {

                $percentageOfClaimsSatisfactionByUnit = $totalClaimsReceived != 0 ? number_format(($satisfactionByUnit->total / $totalClaimsReceived) * 100, 2) : 0;

                if ($satisfactionByUnit->name == null) {
                    $satisfactionByUnit->name = $translateWord;
                }

                array_push(
                    $dataClaimsSatisfaction,
                    [
                        "Unit" => json_decode($satisfactionByUnit->name),
                        "total" => $satisfactionByUnit->total,
                        "percentage" => $percentageOfClaimsSatisfactionByUnit
                    ]
                );

            }

        }else{
            //claims satisfaction rate
            $claimsSatisfaction = $this->getClaimsSatisfaction($request);
            $percentageOfClaimsSatisfaction  = $totalClaimsReceived!=0 ?number_format(($claimsSatisfaction / $totalClaimsReceived)*100,2):0;
            $dataClaimsSatisfaction=[
                'total'=>$claimsSatisfaction,
                'taux'=>$percentageOfClaimsSatisfaction,
            ];
        }
        return $dataClaimsSatisfaction;

    }

    public function HighlyClaimsTreatedInTime($request,$translateWord,$totalClaimsReceived){

        if ($request->has('unit_targeted_id')) {

            $highlyClaimsTreatedInTime = $this->getHighlyClaimsResolvedOnTime($request);
            $dataHighlyClaimsTreatedInTime = [];

            foreach ($highlyClaimsTreatedInTime as $allHighlyClaimsTreatedInTimeByUnit) {

                $percentageOfHighlyClaimsTreatedInTimeByUnit = $totalClaimsReceived != 0 ? number_format(($allHighlyClaimsTreatedInTimeByUnit->total / $totalClaimsReceived) * 100, 2) : 0;

                if ($allHighlyClaimsTreatedInTimeByUnit->name == null) {
                    $allHighlyClaimsTreatedInTimeByUnit->name = $translateWord;
                }

                array_push(
                    $dataHighlyClaimsTreatedInTime,
                    [
                        "Unit" => json_decode($allHighlyClaimsTreatedInTimeByUnit->name),
                        "total" => $allHighlyClaimsTreatedInTimeByUnit->total,
                        "percentageOfHighlyClaimsTreatedInTime" => $percentageOfHighlyClaimsTreatedInTimeByUnit
                    ]
                );

            }
        }else{

            //highly claim treated in time
            $highlyClaimsTreatedInTime = $this->getHighlyClaimsResolvedOnTime($request);
            $percentageOfHighlyClaimsTreatedInTime  = $totalClaimsReceived!=0 ?number_format(($highlyClaimsTreatedInTime / $totalClaimsReceived)*100,2):0;

            $dataHighlyClaimsTreatedInTime=[
                'total'=>$highlyClaimsTreatedInTime,
                'percentageOfHighlyClaimsTreatedInTime'=>$percentageOfHighlyClaimsTreatedInTime,
            ];

        }
        return $dataHighlyClaimsTreatedInTime;

    }

    public function ClientSatisfied($request)
    {

        if (!$request->has('unit_targeted_id')) {

            $claimsSatisfaction = $this->getClaimsSatisfaction($request);
            //total of client contacted after treatment
            $clientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request)->count();
            //rate of satisfied client
            $percentageOfClientSatisfied = $clientContactedAfterTreatment != 0 ? number_format(($claimsSatisfaction / $clientContactedAfterTreatment) * 100, 2) : 0;
            $dataClientSatisfied = [
                'percentageOfClientSatisfied'=>$percentageOfClientSatisfied,
            ];
        }else{
            $dataClientSatisfied = [];
        }
        return $dataClientSatisfied;
    }

    public function ClientDissatisfied($request,$claimOfClientDissatisfied)
    {

        if (!$request->has('unit_targeted_id')) {

            //total of client contacted after treatment
            $clientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request)->count();
            $percentageOfClientDissatisfied = $clientContactedAfterTreatment!=0 ?number_format( ($claimOfClientDissatisfied / $clientContactedAfterTreatment)*100,2):0;

            $dataClientDissatisfied = [
                'percentageOfClientDissatisfied'=>$percentageOfClientDissatisfied,
            ];
        }else{
            $dataClientDissatisfied = [];
        }
        return $dataClientDissatisfied;
    }


    public function GlobalReport($request)
    {

        $translateWord = json_encode( [\app()->getLocale()=>"Autres"] );
        //claim received by period
        $totalClaimsReceived = $this->getAllClaimsByPeriod($request)->count();
        //claims treated in time
        $claimsTreatedInTime = $this->ClaimsResolvedOnTime($request,$translateWord,$totalClaimsReceived);
        //claims satisfaction
        $claimsSatisfaction = $this->ClaimsSatisfaction($request,$translateWord,$totalClaimsReceived);
        //highly claim treated in time
        $highlyClaimsTreatedInTime = $this->HighlyClaimsTreatedInTime($request,$translateWord,$totalClaimsReceived);
        //rate of satisfied client
        $percentageOfClientSatisfied = $this->ClientSatisfied($request);
        //total of client dissatisfied
        $claimOfClientDissatisfied = $this->getClaimsDissatisfied($request)->count();
        //rate of Dissatisfied client
        $percentageOfClientDissatisfied = $this->ClientDissatisfied($request,$claimOfClientDissatisfied);









        //rate of treated low medium claim in time
        $lowMediumClaimsTreatedInTime = $this->getLowMediumClaimsResolvedOnTime($request)->count();
        $percentageOfLowMediumClaimsTreatedInTime = $totalClaimsReceived!=0 ? number_format(($lowMediumClaimsTreatedInTime / $totalClaimsReceived)*100,2):0;

        //claim received resolved
        $totalClaimsResolved = $this->getClaimsResolved($request)->count();

        //claim received unresolved
        $totalClaimsUnresolved = $this->getClaimsUnresolved($request)->count();

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


        return [
            'RateOfClaimsTreatedInTime'=>$claimsTreatedInTime,
            'RateOfClaimsSatisfaction'=>$claimsSatisfaction,
            'RateOfHighlyClaimsTreatedInTime'=>$highlyClaimsTreatedInTime,

            'TotalClaimsReceived'=>$totalClaimsReceived,
            'TotalClaimsResolved'=>$totalClaimsResolved,
            'TotalClaimsUnresolved'=>$totalClaimsUnresolved,
            'TotalClaimResolvedOnTime'=>$claimsTreatedInTime,
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
        ];
    }


}
