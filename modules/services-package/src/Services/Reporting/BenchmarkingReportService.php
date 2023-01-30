<?php

namespace Satis2020\ServicePackage\Services\Reporting;


use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;
use Satis2020\ServicePackage\Traits\Metadata;


class BenchmarkingReportService
{

    use Metadata,FilterClaims,DataUserNature;

    public function BenchmarkingReport($request)
    {
        $translateWord = json_encode( [\app()->getLocale()=>"Autres"] );

        $claimBySeverityLevel = $this->RateOfReceivedClaimsBySeverityLevel($request,$translateWord);
        $claimTreatedBySeverityLevel = $this->RateOfTreatedClaimsBySeverityLevel($request);
        $recurringClaimObject = $this->RecurringClaimObject($request,$translateWord);
        $claimsByCategoryClient = $this->ClaimsByCategoryClient($request,$translateWord);
        $claimsByUnit = $this->ClaimsByUnit($request,$translateWord);
        $claimsByTreatmentUnit = $this->ClaimsTreatedByUnit($request,$translateWord);
        $claimsByRequestChanel = $this->ClaimsByRequestChanel($request,$translateWord);


        return [
            'title' => $this->getMetadataByName(Constants::BENCHMARKING_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::BENCHMARKING_REPORTING)->description,
            'RateOfReceivedClaimsBySeverityLevel'=> $claimBySeverityLevel,
            'RateOfTreatedClaimsBySeverityLevel'=> $claimTreatedBySeverityLevel,
            'recurringClaimObject'=> $recurringClaimObject,
            'ClaimsByCategoryClient'=> $claimsByCategoryClient,
            'ClaimsByUnit'=> $claimsByUnit,
            'ClaimsTreatedByUnit'=> $claimsByTreatmentUnit,
            'ClaimsByRequestChanel'=> $claimsByRequestChanel,
        ];
    }

    public function RateOfReceivedClaimsBySeverityLevel($request,$translateWord){

        //rate of claims received by severity level
        $totalClaims = $this->getAllClaimsByPeriod($request)->count();

        $claimBySeverityLevel = $this->getClaimsReceivedBySeverityLevel($this->getAllClaimsByPeriod($request))->get();
        $dataReceived = [];
        foreach($claimBySeverityLevel as $totalSeverityLevel){
            $totalReceived = $totalSeverityLevel->total;
            $rateReceived =  $totalClaims!=0 ?number_format(($totalReceived / $totalClaims)*100,2):0;

            if($totalSeverityLevel->name==null){
                $totalSeverityLevel->name=$translateWord;
            }
            array_push(
                $dataReceived,
                [
                    "severityLevel"=>json_decode($totalSeverityLevel->name),
                    "rate"=>$rateReceived
                ]
            );

        }

        return $dataReceived;

    }

    public function RateOfTreatedClaimsBySeverityLevel($request){

        //claims received with a claimObject by severityLevel
        $claimWithClaimObjBySeverityLevel = $this->getClaimsReceivedWithClaimObjectBySeverityLevel($request)->get();

        //rate of claims treated by severity level
        $claimTreatedBySeverityLevel = $this->getClaimsTreatedBySeverityLevel($request)->get();
        $dataTreated = [];

        foreach($claimWithClaimObjBySeverityLevel as $totalWithClaimObjSeverityLevel){

            $validateClaims = collect($claimTreatedBySeverityLevel)->where('id','=',$totalWithClaimObjSeverityLevel->id)->first();

            if($validateClaims!=null){
                $rateTreated = number_format(($validateClaims->total / $totalWithClaimObjSeverityLevel->total)*100,2);
                $result = [
                    "severityLevel"=>json_decode($totalWithClaimObjSeverityLevel->name),
                    "rate"=>$rateTreated,
                ];
            }else{
                $result = [
                    "severityLevel"=>json_decode($totalWithClaimObjSeverityLevel->name),
                    "rate"=>0,
                ];
            }
            array_push(
                $dataTreated,
                $result
            );

        }

        return $dataTreated;

    }

    public function RecurringClaimObject($request,$translateWord){

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
                    "ClaimsObject"=>json_decode($threeRecurringClaimObject->name),
                    "total"=>$threeRecurringClaimObject->total
                ]
            );

        }
        return $dataRecurringClaimObject;
    }

    public function ClaimsByCategoryClient($request,$translateWord){

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
                    "CategoryClient"=>json_decode($byCategoryClient->name),
                    "total"=>$byCategoryClient->total
                ]
            );

        }
        return $dataClaimsByCategoryClient;
    }

    public function ClaimsByUnit($request,$translateWord){

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
                    "Unit"=>json_decode($byUnit->name),
                    "total"=>$byUnit->total
                ]
            );

        }
        return $dataClaimsByUnit;
    }

    public function ClaimsTreatedByUnit($request,$translateWord){

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
                    "TreatmentUnit"=>json_decode($byTreatmentUnit->name),
                    "total"=>$byTreatmentUnit->total
                ]
            );

        }
        return $dataClaimsByTreatmentUnit;
    }

    public function ClaimsByRequestChanel($request,$translateWord){

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
        return $dataClaimsByRequestChanel;
    }


}
