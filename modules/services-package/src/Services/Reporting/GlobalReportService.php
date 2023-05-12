<?php

namespace Satis2020\ServicePackage\Services\Reporting;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\FilterClaims;
use Satis2020\ServicePackage\Traits\DataUserNature;

class GlobalReportService
{

    use Metadata, FilterClaims, DataUserNature;

    public function GlobalReport($request)
    {

        $translateWord = json_encode([\app()->getLocale() => "Autres"]);
        //claim received by period or unit
        $totalClaimsReceived = $this->getAllClaimsByPeriod($request)->count();
        $totalClaimsReceivedByUnitOrNo = $this->ClaimsByUnit($request, $translateWord);
        //claims treated in time
        $claimsTreatedInTime = $this->ClaimsResolvedOnTime($request, $translateWord, $totalClaimsReceived);
        //claims satisfaction
        $claimsSatisfaction = $this->ClaimsSatisfaction($request, $translateWord, $totalClaimsReceived);
        //highly claim treated in time
        $highlyClaimsTreatedInTime = $this->HighlyClaimsTreatedInTime($request, $translateWord, $totalClaimsReceived);
        //rate of satisfied client
        $percentageOfClientSatisfied = $this->ClientSatisfied($request);
        //total of client dissatisfied
        $claimOfClientDissatisfied = $this->getClaimsDissatisfied($request)->count();
        //rate of Dissatisfied client
        $percentageOfClientDissatisfied = $this->ClientDissatisfied($request, $claimOfClientDissatisfied);
        //claim received resolved
        $totalClaimsResolved = $this->ClaimsResolved($request, $translateWord);
        //claim received unresolved
        $totalClaimsUnresolved = $this->ClaimsUnresolved($request, $translateWord);
        //claim received resolved Late
        $totalClaimResolvedLate = $this->ClaimResolvedLate($request, $translateWord);
        //low medium claim treated in time
        $percentageOfLowMediumClaimsTreatedInTime = $this->ClaimLowMediumClaimsTreatedInTime($request, $translateWord, $totalClaimsReceived);
        //total of client contacted after treatment
        $clientContactedAfterTreatment = $this->ClaimsSatisfactionAfterTreatment($request, $translateWord);

        //3 recurrent object claim
        $recurringClaimObject = $this->RecurringClaimsByClaimObject($request, $translateWord);
        //claim received by category claim
        $totalReceivedClaimsByClaimCategory = $this->ClaimsReceivedByClaimCategory($request, $translateWord, $totalClaimsReceived);
        //claim received by object claim
        $claimReceivedByClaimObject = $this->ClaimsReceivedByClaimObject($request, $translateWord, $totalClaimsReceived);
        //claim received by gender
        $claimReceivedByClientGender = $this->ClaimsReceivedByClientGender($request, $totalClaimsReceived);

        return [
            'title' => $this->getMetadataByName(Constants::GLOBAL_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::GLOBAL_REPORTING)->description,

            'RateOfClaimsTreatedInTime' => $claimsTreatedInTime,
            'RateOfClaimsSatisfaction' => $claimsSatisfaction,
            'RateOfHighlyClaimsTreatedInTime' => $highlyClaimsTreatedInTime,

            'TotalClaimsReceived' => $totalClaimsReceivedByUnitOrNo,
            'TotalClaimsResolved' => $totalClaimsResolved,
            'TotalClaimsUnresolved' => $totalClaimsUnresolved,
            'TotalClaimResolvedOnTime' => $claimsTreatedInTime,
            'TotalClaimResolvedLate' => $totalClaimResolvedLate,
            'RateOLowMediumClaimsTreatedInTime' => $percentageOfLowMediumClaimsTreatedInTime,
            'RecurringClaimsByClaimObject' => $recurringClaimObject,

            'ClaimsReceivedByClaimCategory' => $totalReceivedClaimsByClaimCategory,
            'ClaimsReceivedByClaimObject' => $claimReceivedByClaimObject,
            'ClaimsReceivedByClientGender' => $claimReceivedByClientGender,

            'ClientContactedAfterTreatment' => $clientContactedAfterTreatment,
            'NumberOfClientSatisfied' => $claimsSatisfaction,
            'PercentageOfClientSatisfied' => $percentageOfClientSatisfied,
            'NumberOfClientDissatisfied' => $claimOfClientDissatisfied,
            'PercentageOfClientDissatisfied' => $percentageOfClientDissatisfied,
        ];
    }


    public function RecurringClaimsByClaimObject($request, $translateWord)
    {

        if ($request->has('unit_targeted_id')) {

            //claims by unit
            $getClaimByUnit = $this->getClaimsReceivedByUnit($request)->whereIn('unit_targeted_id', $request->unit_targeted_id)->get();
            $dataRecurringClaimObject = [];
            $index = 0;

            foreach ($getClaimByUnit as $claimByUnit) {
                array_push($dataRecurringClaimObject, [
                    "unit" => json_decode($claimByUnit->name),
                    "allClaimObject" => []
                ]);


                $dataClaimObjectByUnit = $this->getClaimsReceivedByClaimObject($request, $claimByUnit->id)->limit(3)->get();

                foreach ($dataClaimObjectByUnit as $key => $allDataClaimObjectByUnit) {

                    $result["ClaimsObject"] = json_decode($allDataClaimObjectByUnit->name);
                    $result["total"] = $allDataClaimObjectByUnit->total;
                    $result["rank"] = $key + 1;

                    array_push(
                        $dataRecurringClaimObject[$index]["allClaimObject"],
                        $result
                    );
                }
                $index++;
            }
        } else if ($request->has('institutions')) {
            //claims by unit
            $getClaimByUnit = $this->getClaimsReceivedByInstitution($request)->whereIn('institution_targeted_id', $request->institutions)->get();
            $dataRecurringClaimObject = [];
            $index = 0;
            // dump($getClaimByUnit->toArray());
            foreach ($getClaimByUnit as $claimByUnit) {
                array_push($dataRecurringClaimObject, [
                    "unit" => json_decode($claimByUnit->name) ?? ["fr" => $claimByUnit->name],
                    "allClaimObject" => []
                ]);


                $dataClaimObjectByUnit = $this->getClaimsReceivedByClaimObject($request, null, $claimByUnit->id)->limit(3)->get();

                foreach ($dataClaimObjectByUnit as $key => $allDataClaimObjectByUnit) {

                    $result["ClaimsObject"] = json_decode($allDataClaimObjectByUnit->name);
                    $result["total"] = $allDataClaimObjectByUnit->total;
                    $result["rank"] = $key + 1;

                    array_push(
                        $dataRecurringClaimObject[$index]["allClaimObject"],
                        $result
                    );
                }
                $index++;
            }
        } else {

            $recurringClaimObject = $this->getClaimsReceivedByClaimObject($request)->limit(3)->get();
            $dataRecurringClaimObject = [];

            foreach ($recurringClaimObject as $key => $threeRecurringClaimObject) {

                if ($threeRecurringClaimObject->name == null) {
                    $threeRecurringClaimObject->name = $translateWord;
                }

                array_push(
                    $dataRecurringClaimObject,
                    [
                        "ClaimsObject" => json_decode($threeRecurringClaimObject->name),
                        "total" => $threeRecurringClaimObject->total,
                        "rank" => $key + 1
                    ]
                );
            }
        }


        return $dataRecurringClaimObject;
    }

    public function ClaimsReceivedByClaimCategory($request, $translateWord, $totalClaimsReceived)
    {

        //claim received by category claim
        $totalReceivedClaimsByClaimCategory = $this->getClaimsReceivedByClaimCategory($request)->get();
        $dataReceivedClaimsByClaimCategory = [];
        foreach ($totalReceivedClaimsByClaimCategory as $claimReceivedByClaimCategory) {
            $percentage = $totalClaimsReceived != 0 ? number_format(($claimReceivedByClaimCategory->total / $totalClaimsReceived) * 100, 2) : 0;

            if ($claimReceivedByClaimCategory->name == null) {
                $claimReceivedByClaimCategory->name = $translateWord;
            }

            array_push(
                $dataReceivedClaimsByClaimCategory,
                [
                    "CategoryClaims" => json_decode($claimReceivedByClaimCategory->name),
                    "total" => $claimReceivedByClaimCategory->total,
                    "taux" => $percentage
                ]
            );
        }
        return $dataReceivedClaimsByClaimCategory;
    }

    public function ClaimsReceivedByClaimObject($request, $translateWord, $totalClaimsReceived)
    {

        //claim received by object claim
        $claimReceivedByClaimObject = $this->getClaimsReceivedByClaimObject($request)->get();
        $dataClaimReceivedByClaimObject = [];
        foreach ($claimReceivedByClaimObject as $receivedByClaimObject) {
            $percentage = $totalClaimsReceived != 0 ? number_format(($receivedByClaimObject->total / $totalClaimsReceived) * 100, 2) : 0;

            if ($receivedByClaimObject->name == null) {
                $receivedByClaimObject->name = $translateWord;
            }

            array_push(
                $dataClaimReceivedByClaimObject,
                [
                    "ClaimsObject" => json_decode($receivedByClaimObject->name),
                    "total" => $receivedByClaimObject->total,
                    "taux" => $percentage
                ]
            );
        }

        return $dataClaimReceivedByClaimObject;
    }

    public function ClaimsReceivedByClientGender($request, $totalClaimsReceived)
    {

        //claim received by gender
        $claimReceivedByClientGender = $this->getClaimsReceivedByClientGender($request)->get();
        $dataClaimReceivedByClientGender = [];
        foreach ($claimReceivedByClientGender as $receivedByClientGender) {
            $percentage = $totalClaimsReceived != 0 ? number_format(($receivedByClientGender->total / $totalClaimsReceived) * 100, 2) : 0;

            if ($receivedByClientGender->sexe == null) {
                $receivedByClientGender->sexe = "Autres";
            }

            array_push(
                $dataClaimReceivedByClientGender,
                [
                    "ClientGender" => $receivedByClientGender->sexe,
                    "total" => $receivedByClientGender->total,
                    "taux" => $percentage
                ]
            );
        }
        return $dataClaimReceivedByClientGender;
    }

    public function ClaimsResolvedOnTime($request, $translateWord, $totalClaimsReceived)
    {

        if ($request->has('unit_targeted_id')) {

            $claimsTreatedInTimeByUnit = $this->getClaimsResolvedOnTime($request);
            $dataClaimsTreatedInTime = [];

            foreach ($claimsTreatedInTimeByUnit as $treatedInTimeByUnit) {

                $percentageOfClaimsTreatedInTimeByUnit  = $totalClaimsReceived != 0 ? number_format(($treatedInTimeByUnit->total / $totalClaimsReceived) * 100, 2) : 0;
                if ($treatedInTimeByUnit->name == null) {
                    $treatedInTimeByUnit->name = $translateWord;
                }

                array_push(
                    $dataClaimsTreatedInTime,
                    [
                        "UnitId" => $treatedInTimeByUnit->id,
                        "Unit" => json_decode($treatedInTimeByUnit->name),
                        "total" => $treatedInTimeByUnit->total,
                        "taux" => $percentageOfClaimsTreatedInTimeByUnit
                    ]
                );
            }

            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimsTreatedInTime, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClaimsTreatedInTime,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0",
                            "taux" => "0.00"
                        ]
                    );
                }
            }
        } else if ($request->has('institutions')) {
            $claimsTreatedInTimeByUnit = $this->getClaimsResolvedOnTime($request);
            $dataClaimsTreatedInTime = [];

            foreach ($claimsTreatedInTimeByUnit as $treatedInTimeByUnit) {

                $percentageOfClaimsTreatedInTimeByUnit  = $totalClaimsReceived != 0 ? number_format(($treatedInTimeByUnit->total / $totalClaimsReceived) * 100, 2) : 0;

                if ($treatedInTimeByUnit->name == null) {
                    $treatedInTimeByUnit->name = $translateWord;
                }
                //dump($treatedInTimeByUnit->name);
                array_push(
                    $dataClaimsTreatedInTime,
                    [
                        "UnitId" => $treatedInTimeByUnit->id,
                        "Unit" => json_decode($treatedInTimeByUnit->name) ?? ["fr" => $treatedInTimeByUnit->name],
                        "total" => $treatedInTimeByUnit->total,
                        "taux" => $percentageOfClaimsTreatedInTimeByUnit
                    ]
                );
            }

            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkInstitutionInArray($dataClaimsTreatedInTime, $unitId);

                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClaimsTreatedInTime,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0",
                            "taux" => "0.00"
                        ]
                    );
                }
            }
        } else {

            //rate of claims treated in time
            $claimsTreatedInTime = $this->getClaimsResolvedOnTime($request);
            $dataClaimsTreatedInTime  = $totalClaimsReceived != 0 ? number_format(($claimsTreatedInTime / $totalClaimsReceived) * 100, 2) : 0;
            $dataClaimsTreatedInTime = [
                'total' => $claimsTreatedInTime,
                'taux' => $dataClaimsTreatedInTime,
            ];
        }

        return $dataClaimsTreatedInTime;
    }

    public function ClaimsSatisfaction($request, $translateWord, $totalClaimsReceived)
    {

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
                        "UnitId" => $satisfactionByUnit->id,
                        "Unit" => json_decode($satisfactionByUnit->name),
                        "total" => $satisfactionByUnit->total,
                        "taux" => $percentageOfClaimsSatisfactionByUnit
                    ]
                );
            }

            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimsSatisfaction, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClaimsSatisfaction,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0",
                            "taux" => "0.00"
                        ]
                    );
                }
            }
        } else if ($request->has('institutions')) {

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
                        "UnitId" => $satisfactionByUnit->id,
                        "Unit" => json_decode($satisfactionByUnit->name) ?? ["fr" => $satisfactionByUnit->name],
                        "total" => $satisfactionByUnit->total,
                        "taux" => $percentageOfClaimsSatisfactionByUnit
                    ]
                );
            }

            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkInstitutionInArray($dataClaimsSatisfaction, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClaimsSatisfaction,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0",
                            "taux" => "0.00"
                        ]
                    );
                }
            }
        } else {
            //claims satisfaction rate
            $claimsSatisfaction = $this->getClaimsSatisfaction($request);
            $percentageOfClaimsSatisfaction  = $totalClaimsReceived != 0 ? number_format(($claimsSatisfaction / $totalClaimsReceived) * 100, 2) : 0;
            $dataClaimsSatisfaction = [
                'total' => $claimsSatisfaction,
                'taux' => $percentageOfClaimsSatisfaction,
            ];
        }
        return $dataClaimsSatisfaction;
    }

    public function HighlyClaimsTreatedInTime($request, $translateWord, $totalClaimsReceived)
    {

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
                        "taux" => $percentageOfHighlyClaimsTreatedInTimeByUnit
                    ]
                );
            }
        } else  if ($request->has('institutions')) {

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
                        "Unit" => json_decode($allHighlyClaimsTreatedInTimeByUnit->name) ?? ["fr" => $allHighlyClaimsTreatedInTimeByUnit->name],
                        "total" => $allHighlyClaimsTreatedInTimeByUnit->total,
                        "taux" => $percentageOfHighlyClaimsTreatedInTimeByUnit
                    ]
                );
            }
        } else {

            //highly claim treated in time
            $highlyClaimsTreatedInTime = $this->getHighlyClaimsResolvedOnTime($request);
            $percentageOfHighlyClaimsTreatedInTime  = $totalClaimsReceived != 0 ? number_format(($highlyClaimsTreatedInTime / $totalClaimsReceived) * 100, 2) : 0;

            $dataHighlyClaimsTreatedInTime = [
                'total' => $highlyClaimsTreatedInTime,
                'taux' => $percentageOfHighlyClaimsTreatedInTime,
            ];
        }
        return $dataHighlyClaimsTreatedInTime;
    }

    public function ClientSatisfied($request)
    {

        if (!$request->has('unit_targeted_id') && !$request->has('institutions')) {

            $claimsSatisfaction = $this->getClaimsSatisfaction($request);
            //total of client contacted after treatment
            $clientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request)->count();
            //rate of satisfied client
            $percentageOfClientSatisfied = $clientContactedAfterTreatment != 0 ? number_format(($claimsSatisfaction / $clientContactedAfterTreatment) * 100, 2) : 0;
            $dataClientSatisfied = [
                'taux' => $percentageOfClientSatisfied,
            ];
        } else {
            $dataClientSatisfied = [];
        }
        return $dataClientSatisfied;
    }

    public function ClientDissatisfied($request, $claimOfClientDissatisfied)
    {

        if (!$request->has('unit_targeted_id') && !$request->has('institutions')) {

            //total of client contacted after treatment
            $clientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request)->count();
            $percentageOfClientDissatisfied = $clientContactedAfterTreatment != 0 ? number_format(($claimOfClientDissatisfied / $clientContactedAfterTreatment) * 100, 2) : 0;

            $dataClientDissatisfied = [
                'taux' => $percentageOfClientDissatisfied,
            ];
        } else {
            $dataClientDissatisfied = [];
        }
        return $dataClientDissatisfied;
    }

    public function ClaimsByUnit($request, $translateWord)
    {

        if ($request->has('unit_targeted_id')) {

            $getClaimByUnit = $this->getClaimsReceivedByUnit($request)->whereIn('unit_targeted_id', $request->unit_targeted_id)->get();

            $dataClaimByUnit = [];

            foreach ($getClaimByUnit as $ClaimByUnit) {

                if ($ClaimByUnit->name == null) {
                    $ClaimByUnit->name = $translateWord;
                }

                array_push(
                    $dataClaimByUnit,
                    [
                        "UnitId" => $ClaimByUnit->id,
                        "Unit" => json_decode($ClaimByUnit->name),
                        "total" => $ClaimByUnit->total
                    ]
                );
            }

            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimByUnit, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClaimByUnit,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        }
        if ($request->has('institutions')) {

            $getClaimByUnit = $this->getClaimsReceivedByUnit($request)->whereIn('institution_targeted_id', $request->institutions)->get();

            $dataClaimByUnit = [];

            foreach ($getClaimByUnit as $ClaimByUnit) {

                if ($ClaimByUnit->name == null) {
                    $ClaimByUnit->name = $translateWord;
                }

                array_push(
                    $dataClaimByUnit,
                    [
                        "UnitId" => $ClaimByUnit->id,
                        "Unit" => json_decode($ClaimByUnit->name),
                        "total" => $ClaimByUnit->total
                    ]
                );
            }

            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkInstitutionInArray($dataClaimByUnit, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClaimByUnit,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        } else {
            $dataClaimByUnit = $this->getAllClaimsByPeriod($request)->count();
        }
        return $dataClaimByUnit;
    }

    public function ClaimsResolved($request, $translateWord)
    {

        if ($request->has('unit_targeted_id')) {

            $getClaimsResolved = $this->getClaimsResolved($request);
            $dataClaimResolved = [];

            foreach ($getClaimsResolved as $ClaimResolved) {

                if ($ClaimResolved->name == null) {
                    $ClaimResolved->name = $translateWord;
                }

                array_push(
                    $dataClaimResolved,
                    [
                        "UnitId" => $ClaimResolved->id,
                        "Unit" => json_decode($ClaimResolved->name),
                        "total" => $ClaimResolved->total
                    ]
                );
            }

            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimResolved, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClaimResolved,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        }
        if ($request->has('institutions')) {

            $getClaimsResolved = $this->getClaimsResolved($request);
            $dataClaimResolved = [];

            foreach ($getClaimsResolved as $ClaimResolved) {

                if ($ClaimResolved->name == null) {
                    $ClaimResolved->name = $translateWord;
                }

                array_push(
                    $dataClaimResolved,
                    [
                        "UnitId" => $ClaimResolved->id,
                        "Unit" => json_decode($ClaimResolved->name) ?? ["fr" => $ClaimResolved->name],
                        "total" => $ClaimResolved->total
                    ]
                );
            }

            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkInstitutionInArray($dataClaimResolved, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClaimResolved,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        } else {
            $dataClaimResolved = $this->getClaimsResolved($request);
        }
        return $dataClaimResolved;
    }

    public function ClaimsUnresolved($request, $translateWord)
    {

        if ($request->has('unit_targeted_id')) {

            $getClaimsUnresolved = $this->getClaimsUnresolved($request);
            $dataClaimUnresolved = [];

            foreach ($getClaimsUnresolved as $ClaimUnresolved) {

                if ($ClaimUnresolved->name == null) {
                    $ClaimUnresolved->name = $translateWord;
                }

                array_push(
                    $dataClaimUnresolved,
                    [
                        "UnitId" => $ClaimUnresolved->id,
                        "Unit" => json_decode($ClaimUnresolved->name),
                        "total" => $ClaimUnresolved->total
                    ]
                );
            }

            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimUnresolved, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClaimUnresolved,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        }
        if ($request->has('institutions')) {

            $getClaimsUnresolved = $this->getClaimsUnresolved($request);
            $dataClaimUnresolved = [];

            foreach ($getClaimsUnresolved as $ClaimUnresolved) {

                if ($ClaimUnresolved->name == null) {
                    $ClaimUnresolved->name = $translateWord;
                }

                array_push(
                    $dataClaimUnresolved,
                    [
                        "UnitId" => $ClaimUnresolved->id,
                        "Unit" => json_decode($ClaimUnresolved->name) ?? ["fr" => $ClaimUnresolved->name],
                        "total" => $ClaimUnresolved->total
                    ]
                );
            }

            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimUnresolved, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClaimUnresolved,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        } else {
            $dataClaimUnresolved = $this->getClaimsUnresolved($request);
        }
        return $dataClaimUnresolved;
    }

    public function ClaimResolvedLate($request, $translateWord)
    {

        if ($request->has('unit_targeted_id')) {

            $getClaimsResolvedLate = $this->getClaimsResolvedLate($request);
            $dataClaimsResolvedLate = [];

            foreach ($getClaimsResolvedLate as $claimsResolvedLate) {

                if ($claimsResolvedLate->name == null) {
                    $claimsResolvedLate->name = $translateWord;
                }

                array_push(
                    $dataClaimsResolvedLate,
                    [
                        "UnitId" => $claimsResolvedLate->id,
                        "Unit" => json_decode($claimsResolvedLate->name),
                        "total" => $claimsResolvedLate->total
                    ]
                );
            }


            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimsResolvedLate, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClaimsResolvedLate,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        }
        if ($request->has('institutions')) {

            $getClaimsResolvedLate = $this->getClaimsResolvedLate($request);
            $dataClaimsResolvedLate = [];

            foreach ($getClaimsResolvedLate as $claimsResolvedLate) {

                if ($claimsResolvedLate->name == null) {
                    $claimsResolvedLate->name = $translateWord;
                }

                array_push(
                    $dataClaimsResolvedLate,
                    [
                        "UnitId" => $claimsResolvedLate->id,
                        "Unit" => json_decode($claimsResolvedLate->name) ?? ["fr" => $claimsResolvedLate->name],
                        "total" => $claimsResolvedLate->total
                    ]
                );
            }


            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimsResolvedLate, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClaimsResolvedLate,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        } else {
            $dataClaimsResolvedLate = $this->getClaimsResolvedLate($request);
        }
        return $dataClaimsResolvedLate;
    }

    public function ClaimLowMediumClaimsTreatedInTime($request, $translateWord, $totalClaimsReceived)
    {

        if ($request->has('unit_targeted_id')) {

            $getClaimsLowMediumClaimsTreatedInTime = $this->getLowMediumClaimsResolvedOnTime($request);
            $dataClaimsLowMediumClaimsTreatedInTime = [];

            foreach ($getClaimsLowMediumClaimsTreatedInTime as $claimsLowMediumClaimsTreatedInTime) {

                $percentageOfLowMediumClaimsTreatedInTime = $totalClaimsReceived != 0 ? number_format(($claimsLowMediumClaimsTreatedInTime->total / $totalClaimsReceived) * 100, 2) : 0;

                if ($claimsLowMediumClaimsTreatedInTime->name == null) {
                    $claimsLowMediumClaimsTreatedInTime->name = $translateWord;
                }

                array_push(
                    $dataClaimsLowMediumClaimsTreatedInTime,
                    [
                        "UnitId" => $claimsLowMediumClaimsTreatedInTime->id,
                        "Unit" => json_decode($claimsLowMediumClaimsTreatedInTime->name),
                        "taux" => $percentageOfLowMediumClaimsTreatedInTime
                    ]
                );
            }

            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimsLowMediumClaimsTreatedInTime, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClaimsLowMediumClaimsTreatedInTime,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "taux" => "0.00"
                        ]
                    );
                }
            }
        }
        else if ($request->has('institutions')) {

            $getClaimsLowMediumClaimsTreatedInTime = $this->getLowMediumClaimsResolvedOnTime($request);
            $dataClaimsLowMediumClaimsTreatedInTime = [];

            foreach ($getClaimsLowMediumClaimsTreatedInTime as $claimsLowMediumClaimsTreatedInTime) {

                $percentageOfLowMediumClaimsTreatedInTime = $totalClaimsReceived != 0 ? number_format(($claimsLowMediumClaimsTreatedInTime->total / $totalClaimsReceived) * 100, 2) : 0;

                if ($claimsLowMediumClaimsTreatedInTime->name == null) {
                    $claimsLowMediumClaimsTreatedInTime->name = $translateWord;
                }

                array_push(
                    $dataClaimsLowMediumClaimsTreatedInTime,
                    [
                        "UnitId" => $claimsLowMediumClaimsTreatedInTime->id,
                        "Unit" => json_decode($claimsLowMediumClaimsTreatedInTime->name) ??  ["fr" => $claimsLowMediumClaimsTreatedInTime->name],
                        "taux" => $percentageOfLowMediumClaimsTreatedInTime
                    ]
                );
            }

            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClaimsLowMediumClaimsTreatedInTime, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClaimsLowMediumClaimsTreatedInTime,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "taux" => "0.00"
                        ]
                    );
                }
            }
        } else {
            $lowMediumClaimsTreatedInTime = $this->getLowMediumClaimsResolvedOnTime($request);
            $percentageOfLowMediumClaimsTreatedInTime = $totalClaimsReceived != 0 ? number_format(($lowMediumClaimsTreatedInTime / $totalClaimsReceived) * 100, 2) : 0;
            $dataClaimsLowMediumClaimsTreatedInTime = [
                'taux' => $percentageOfLowMediumClaimsTreatedInTime,
            ];
        }
        return $dataClaimsLowMediumClaimsTreatedInTime;
    }

    public function ClaimsSatisfactionAfterTreatment($request, $translateWord)
    {

        if ($request->has('unit_targeted_id')) {

            $getClientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request);
            $dataClientContactedAfterTreatment = [];

            foreach ($getClientContactedAfterTreatment as $clientContactedAfterTreatment) {

                if ($clientContactedAfterTreatment->name == null) {
                    $clientContactedAfterTreatment->name = $translateWord;
                }

                array_push(
                    $dataClientContactedAfterTreatment,
                    [
                        "UnitId" => $clientContactedAfterTreatment->id,
                        "Unit" => json_decode($clientContactedAfterTreatment->name),
                        "total" => $clientContactedAfterTreatment->total
                    ]
                );
            }

            foreach ($request->unit_targeted_id as $unitId) {
                $idExistInUnit = $this->checkUnitInArray($dataClientContactedAfterTreatment, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Unit::findOrFail($unitId);
                    array_push(
                        $dataClientContactedAfterTreatment,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        }
        if ($request->has('institutions')) {

            $getClientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request);
            $dataClientContactedAfterTreatment = [];

            foreach ($getClientContactedAfterTreatment as $clientContactedAfterTreatment) {

                if ($clientContactedAfterTreatment->name == null) {
                    $clientContactedAfterTreatment->name = $translateWord;
                }

                array_push(
                    $dataClientContactedAfterTreatment,
                    [
                        "UnitId" => $clientContactedAfterTreatment->id,
                        "Unit" => json_decode($clientContactedAfterTreatment->name),
                        "total" => $clientContactedAfterTreatment->total
                    ]
                );
            }

            foreach ($request->institutions as $unitId) {
                $idExistInUnit = $this->checkInstitutionInArray($dataClientContactedAfterTreatment, $unitId);
                if (!$idExistInUnit) {
                    $unit =  Institution::findOrFail($unitId);
                    array_push(
                        $dataClientContactedAfterTreatment,
                        [
                            "UnitId" => $unit->id,
                            "Unit" => ["fr" => $unit->name],
                            "total" => "0"
                        ]
                    );
                }
            }
        } else {
            $dataClientContactedAfterTreatment = $this->getClaimsSatisfactionAfterTreatment($request)->count();
        }
        return $dataClientContactedAfterTreatment;
    }


    public function checkUnitInArray($units, $unitId)
    {

        foreach ($units as $unit) {
            if ($unit["UnitId"] == $unitId) {
                return true;
                break;
            }
        }
        return false;
    }

    public function checkInstitutionInArray($units, $unitId)
    {

        foreach ($units as $unit) {
            if ($unit["UnitId"] == $unitId) {
                return true;
                break;
            }
        }
        return false;
    }
}
