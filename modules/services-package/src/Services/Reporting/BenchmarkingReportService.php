<?php

namespace Satis2020\ServicePackage\Services\Reporting;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;


class BenchmarkingReportService
{

    use FilterClaims,DataUserNature;

    public function BenchmarkingReport($request)
    {
        $translateWord = json_encode( [\app()->getLocale()=>"Autres"] );

        //rate of claims received by severity level
        $totalClaims = $this->getAllClaimsByPeriod($request)->count();
        $claimBySeverityLevel = $this->getClaimsReceivedBySeverityLevel($request)->get();
        $dataReceived = [];
        foreach($claimBySeverityLevel as $totalSeverityLevel){

            $totalReceived = $totalSeverityLevel->total;
            $result = ($totalReceived / $totalClaims)*100;
            $rateReceived = number_format((float)$result,2, '.', '');
            if($totalSeverityLevel->name==null){
                $totalSeverityLevel->name=$translateWord;
            }
            array_push(
                $dataReceived,
                [
                    "severityLevel"=>$totalSeverityLevel->name,
                    "rate"=>$rateReceived
                ]
            );

        }

        //claims received with a claimObject by severityLevel
        $claimWithClaimObjBySeverityLevel = $this->getClaimsReceivedWithClaimObjectBySeverityLevel($request)->get();
        //rate of claims treated by severity level
        $claimTreatedBySeverityLevel = $this->getClaimsTreatedBySeverityLevel($request)->get();
        $dataTreated = [];
        foreach($claimWithClaimObjBySeverityLevel as $totalWithClaimObjSeverityLevel){

            $validateClaims = collect($claimTreatedBySeverityLevel)->where('id','=',$totalWithClaimObjSeverityLevel->id)->first();

            if($validateClaims!=null){
                $result = ($validateClaims->total / $totalWithClaimObjSeverityLevel->total)*100;
                $rateTreated = number_format((float)$result, 2, '.', '');
                $result = [
                    "severityLevel"=>$totalWithClaimObjSeverityLevel->name,
                    "rate"=>$rateTreated,
                ];
            }else{
                    $result = [
                        "severityLevel"=>$totalWithClaimObjSeverityLevel->name,
                        "rate"=>0,
                    ];
            }
           array_push(
               $dataTreated,
                    $result
                );

        }

        //recurrent object claim
        $recurringClaimObject = $this->getClaimsReceivedByClaimObject($request)->get();
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

        //Sum of claims received by category client
        $claimsByCategoryClient = $this->getClaimsReceivedByClientCategory($request)->get();
        $dataClaimsByCategoryClient = [];
        foreach($claimsByCategoryClient as $byCategoryClient){

            if($byCategoryClient->name==null){
                $byCategoryClient->name=$translateWord;
            }

            array_push(
                $dataClaimsByCategoryClient,
                [
                    "CategoryClient"=>$byCategoryClient->name,
                    "total"=>$byCategoryClient->total
                ]
            );

        }

        //Sum of claims received by unit
        $claimsByUnit = $this->getClaimsReceivedByUnit($request)->get();
        $dataClaimsByUnit = [];
        foreach($claimsByUnit as $byUnit){

            if($byUnit->name==null){
                $byUnit->name=$translateWord;
            }

            array_push(
                $dataClaimsByUnit,
                [
                    "Unit"=>$byUnit->name,
                    "total"=>$byUnit->total
                ]
            );

        }

        //Sum of claims treated by unit
        $claimsByTreatmentUnit = $this->getClaimsTreatedByUnit($request)->get();
        $dataClaimsByTreatmentUnit = [];
        foreach($claimsByTreatmentUnit as $byTreatmentUnit){

            if($byTreatmentUnit->name==null){
                $byTreatmentUnit->name=$translateWord;
            }

            array_push(
                $dataClaimsByTreatmentUnit,
                [
                    "TreatmentUnit"=>$byTreatmentUnit->name,
                    "total"=>$byTreatmentUnit->total
                ]
            );

        }

        //Sum of claims by request channel
        $claimsByRequestChanel = $this->getClaimsByRequestChanel($request)->get();
        $dataClaimsByRequestChanel = [];
        foreach($claimsByRequestChanel as $byRequestChanel){

            if($byRequestChanel->slug==null){
                $byRequestChanel->slug=$translateWord;
            }

            array_push(
                $dataClaimsByRequestChanel,
                [
                    "RequestChanel"=>$byRequestChanel->slug,
                    "total"=>$byRequestChanel->total
                ]
            );

        }

        return [
                 'RateOfReceivedClaimsBySeverityLevel'=> $dataReceived,
                 'RateOfTreatedClaimsBySeverityLevel'=> $dataTreated,
                 'recurringClaimObject'=> $dataRecurringClaimObject,
                 'ClaimsByCategoryClient'=> $dataClaimsByCategoryClient,
                 'ClaimsByUnit'=> $dataClaimsByUnit,
                 'ClaimsTreatedByUnit'=> $dataClaimsByTreatmentUnit,
                 'ClaimsByRequestChanel'=> $dataClaimsByRequestChanel,
               ];
    }


}
