<?php
namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Models\Claim;

/**
 * Trait UemoaReports
 * @package Satis2020\ServicePackage\Traits
 */
trait UemoaReports{

    /**
     * @return array
     */
    protected function getRelations()
    {
        $relations = [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite','activeTreatment.responsibleUnit', 'activeTreatment.assignedToStaffBy.identite'
        ];

        return $relations;
    }
    /**
     * @return array
     */
    protected function rulePeriode(){

        $data = [

            'date_start' => 'required|date_format:Y-m-d',
            'date_end' => 'required|date_format:Y-m-d|after:date_start'
        ];

        return $data;

    }

    /**
     * @param $request
     * @return mixed
     */
    protected function periodeParams($request){

        $periode['date_start'] = Carbon::parse($request->date_start)->startOfDay();
        $periode['date_end']  = Carbon::parse($request->date_end)->endOfDay();

        return $periode;
    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @return mixed
     */
    protected function getAllClaimByPeriode($request, $myInstitution = false){

        $periode = $this->periodeParams($request);

        $date_start = $periode['date_start'];
        $date_end = $periode['date_end'];

        $claims = Claim::with($this->getRelations())->whereBetween('created_at', [$date_start, $date_end]);

        if($myInstitution){

            $claims = $claims->where('institution_targeted_id', $this->institution()->id);
        }

        return $claims;

    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @return \Illuminate\Support\Collection
     */
    protected function resultatsGlobalState($request, $myInstitution = false){

        $datas = collect([]);

        $claims = $this->getAllClaimByPeriode($request, $myInstitution)->get();

        foreach ($claims as $claim){

            $data = $this->tabDatas($claim);

            $data = Arr::except($data, 'telephone');

            if(!$myInstitution){

                $data = Arr::prepend($data, $claim->institutionTargeted->name, 'filiale');
            }

            $datas->push($data);
        }

        return $datas;
    }



    /**
     * @param $request
     * @param bool $myInstitution
     * @return \Illuminate\Support\Collection
     */
    protected function resultatsStateMore30Days($request, $myInstitution = false){

        $datas = collect([]);

        $claims = $this->getAllClaimByPeriode($request, $myInstitution)->get()->filter(function ($item){

            return ($item->created_at->copy()->diffInDays(now()) >= 30) && ($item->activeTreatment && $item->activeTreatment->validated_at);

        })->values();

        foreach ($claims as $claim){

            $data = $this->tabDatas($claim);

            if(!$myInstitution){

                $data = Arr::prepend($data, $claim->institutionTargeted->name, 'filiale');
            }

            $datas->push($data);
        }

        return $datas;

    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @return \Illuminate\Support\Collection
     */
    protected function resultatsStateOutTime($request, $myInstitution = false){

        $datas = collect([]);

        $claims = $this->getAllClaimByPeriode($request, $myInstitution)->get()->filter(function ($item){

            return ($item->created_at->copy()->diffInDays(now()) > $item->time_limit) && ($item->activeTreatment && !$item->activeTreatment->validated_at);

        })->values();

        foreach ($claims as $claim){

            $data = $this->tabDatas($claim);

            if(!$myInstitution){

                $data = Arr::prepend($data, $claim->institutionTargeted->name, 'filiale');
            }

            $datas->push($data);
        }

        return $datas;

    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @return array
     */
    protected function resultatsStateAnalytique($request, $myInstitution = false){

        $claims = $this->getAllClaimByPeriode($request, $myInstitution)->get()->groupBy([
            function ($item) {
                return $item->institutionTargeted->name;
            },
            'accountType',
            function($item){
                return $item->claimObject->claimCategory->name;
            },
            function($item){
                return $item->claimObject->name;
            }
        ]);

        $claimCollection = collect([]);

        $claims = $claims->map(function ($itemInstitution, $keyInstitution) use ($claimCollection, $myInstitution){

            $itemInstitution = $itemInstitution->map(function ($itemTypeClient, $keyTypeClient) use ($claimCollection, $keyInstitution,$myInstitution){


                $itemTypeClient = $itemTypeClient->map(function ($itemCategoryObject, $keyCategoryObject) use ($claimCollection, $keyInstitution, $keyTypeClient,$myInstitution){


                    $itemCategoryObject = $itemCategoryObject->map(function ($itemObject, $keyObject) use ($claimCollection, $keyInstitution, $keyTypeClient, $keyCategoryObject,$myInstitution){


                        $itemObject = $itemObject->map(function ($itemClaim, $keyClaim) use ($claimCollection, $keyInstitution, $keyTypeClient, $keyCategoryObject, $keyObject, $itemObject,$myInstitution){

                            $data = [
                                'filiale ' => $keyInstitution,
                                'typeClient ' => $keyTypeClient,
                                'claimCategorie ' => $keyCategoryObject,
                                'claimObject ' => $keyObject,
                                'totalClaim' => $itemObject->count(),
                                'totalTreated' => $this->totalTreated($itemObject),
                                'totalUnfounded' => $this->totalUnfounded($itemObject),
                                'totalNoValidated' => $this->totalNoValidated($itemObject),
                                'delayMediumQualification' => $this->delayMediumQualification($itemObject),
                                'delayPlanned' => $itemObject->first()->claimObject->time_limit,
                                'delayMediumTreatmentOpenDay' => $this->delayMediumTreatmentOpenDay($itemObject),
                                'delayMediumTreatmentWorkingDay' => $this->delayMediumTreatmentWorkingDay($itemObject),
                                'percentageTreatedInDelay' => $this->percentageInTime($itemObject),
                                'percentageTreatedOutDelay' => $this->percentageOutTime($itemObject),
                                'percentageNoTreated' => $this->percentageNotTreated($itemObject)
                            ];

                            if(!$myInstitution){

                                $data = Arr::except($data, 'filiale');

                            }

                            $claimCollection->push($data);

                        });

                    });

                });

            });

        });

        return $claimCollection;

    }


    /**
     * @param $claim
     * @return array
     */
    protected function tabDatas($claim){

        return  [
            'typeClient' => $claim->accountType,
            'client' => $this->client($claim),
            'account' => $claim->accountTargeted ? $claim->accountTargeted->number : '',
            'telephone' => $this->telephone($claim),
            'agence' =>  $claim->unitTargeted ? $claim->unitTargeted->name : '',
            'claimCategorie' => $claim->claimObject->claimCategory->name,
            'claimObject' => $claim->claimObject->name,
            'requestChannel' => $claim->requestChannel->name,
            'commentClient' => $claim->description,
            'staffTreating' => $this->staffTreating($claim),
            'solution' => $this->solution($claim),
            'status' => $this->status($claim),
            'dateRegister' => $claim->created_at->copy()->format('d/m/y'),
            'dateQualification' => $this->dateQualification($claim),
            'dateTreatment' => $this->dateTreatment($claim),
            'dateClosing' => $this->closing($claim),
            'delayQualificationOpenDay' => $this->delayQualification($claim)['open_day'],
            'delayQualificationWorkingDay' => $this->delayQualification($claim)['working_day'],
            'delayTreatmentOpenDay' => $this->delayTreatment($claim)['open_day'],
            'delayTreatmentWorkingDay' => $this->delayTreatment($claim)['working_day'],
            'amountDisputed' =>  $claim->amount_disputed,
            'accountCurrency' => $this->currency($claim)
        ];
    }

    /**
     * @param $claim
     * @return string|null
     */
    protected function client($claim){

        $client = null;

        $claim->claimer ? $client = $claim->claimer->firstname.' '.$claim->claimer->lastname : null;

        return $client;
    }


    /**
     * @param $claim
     * @return string|null
     */
    protected function telephone($claim){

        $telephone = null;

        ($claim->claimer && $claim->claimer->telephone) ? $telephone = implode(" ",$claim->claimer->telephone) : null;

        return $telephone;
    }


    /**
     * @param $claim
     * @return null
     */
    protected function currency($claim){

        $currency = null;

        $claim->amountCurrency ? $currency = $claim->amountCurrency->name : null;

        return $currency;
    }
    /**
     * @param $claim
     * @return string
     */
    protected function dateQualification($claim){

        return ($claim->activeTreatment && $claim->activeTreatment->transferred_to_unit_at) ?
            $claim->activeTreatment->transferred_to_unit_at->copy()->format('d/m/y') : null;
    }

    /**
     * @param $claim
     * @return string
     */
    protected function dateTreatment($claim){

        return ($claim->activeTreatment && $claim->activeTreatment->validated_at) ? $claim->activeTreatment->validated_at->copy()->format('d/m/y') : null;
    }


    /**
     * @param $claim
     * @return string
     */
    protected function status($claim){

        return $claim->status ? $claim->status : null;
    }

    /**
     * @param $claim
     * @return string
     */
    protected function staffTreating($claim){

        $staff = '';

        ($claim->activeTreatment && $claim->activeTreatment->responsibleUnit) ?
            $staff .= $claim->activeTreatment->responsibleUnit->name : null;

        ($claim->activeTreatment && $claim->activeTreatment->responsibleStaff) ?
            $staff .= ' - ' .$claim->activeTreatment->responsibleStaff->identite->firstname.' '.$claim->activeTreatment->responsibleStaff->identite->lastname : null;

        return $staff;
    }

    /**
     * @param $claim
     * @return string
     */
    protected function solution($claim){

        return ($claim->activeTreatment && $claim->activeTreatment->validated_at) ?
            $claim->activeTreatment->solution : null;
    }

    /**
     * @param $claim
     * @return string
     */
    protected function closing($claim){

        $dateClosing = null;

        if($claim->status === "archived"){

            if($claim->activeTreatment && $claim->activeTreatment->satisfaction_measured_at){

                $dateClosing = $claim->activeTreatment->satisfaction_measured_at->copy()->format('d/m/y');

            }elseif($claim->activeTreatment && $claim->activeTreatment->validated_at){

                $dateClosing = $claim->activeTreatment->validated_at->copy()->format('d/m/y');
            }

        }

        return $dateClosing;
    }


    /**
     * @param $claim
     * @return array |null
     */
    protected function delayQualification($claim){

        $open_day = null;
        $working_day = null;

        if($claim->activeTreatment && $claim->activeTreatment->transferred_to_unit_at){
            $open_day = $this->diffInWorkingDays($claim->created_at->copy(), $claim->activeTreatment->transferred_to_unit_at->copy());
            $working_day = $claim->created_at->copy()->diffInDays($claim->activeTreatment->transferred_to_unit_at->copy());
        }

        return [
            'open_day' => $open_day,
            'working_day' => $working_day
        ];
    }

    /**
     * @param $claim
     * @return |null
     */
    protected function delayTreatment($claim){

        $open_day = null;
        $working_day = null;

        if($claim->activeTreatment && $claim->activeTreatment->transferred_to_unit_at && $claim->activeTreatment->validated_at){

            $open_day = $claim->activeTreatment->transferred_to_unit_at->copy()->diffInDays(($claim->activeTreatment->validated_at->copy()));
            $working_day = $this->diffInWorkingDays($claim->activeTreatment->transferred_to_unit_at->copy(), $claim->activeTreatment->validated_at->copy());
        }

        return [
            'open_day' => $open_day,
            'working_day' => $working_day
        ];
    }


    /**
     * @param $start
     * @param $end
     * @return mixed
     */
    protected function diffInWorkingDays($start, $end){

        return $start->diffInDaysFiltered(function (Carbon $date) {

            return $date->isWeekday();

        }, $end);

    }

    /**
     * @param $itemObject
     * @return float|int
     */
    protected function percentageNotTreated($itemObject){

        $totalNoValidated = $this->totalNoValidated($itemObject);

        $total= $itemObject->count();

        return ($totalNoValidated && $total) ? (round((($totalNoValidated /$total ) * 100),2)) : 0;
    }


    /**
     * @param $itemObject
     * @return float|int
     */
    protected function percentageOutTime($itemObject){

        $totalTreatedOutDelay = $this->totalTreatedOutDelay($itemObject);

        $totalTreated = $this->totalTreated($itemObject);

        return ($totalTreatedOutDelay && $totalTreated) ? (round((($totalTreatedOutDelay /$totalTreated ) * 100),2)) : 0;
    }

    /**
     * @param $itemObject
     * @return float|int
     */
    protected function percentageInTime($itemObject){

        $totalTreatedInDelay = $this->totalTreatedInDelay($itemObject);

        $totalTreated = $this->totalTreated($itemObject);

        return ($totalTreatedInDelay && $totalTreated) ? (round((($totalTreatedInDelay /$totalTreated ) * 100),2)) : 0;
    }


    /**
     * @param $itemObject
     * @return mixed
     */
    protected function totalTreatedInDelay($itemObject){

        return $itemObject->filter(function ($item){

            return ($item->activeTreatment && $item->activeTreatment->validated_at && ($item->created_at->copy()->addWeekdays($item->time_limit) < $item->activeTreatment->validated_at));

        })->count();
    }


    /**
     * @param $itemObject
     * @return mixed
     */
    protected function totalTreatedOutDelay($itemObject){

        return $itemObject->filter(function ($item){

            return ($item->activeTreatment && $item->activeTreatment->validated_at && ($item->created_at->copy()->addWeekdays($item->time_limit) > $item->activeTreatment->validated_at));

        })->count();
    }


    /**
     * @param $itemObject
     * @return float|int
     */
    protected function delayMediumQualification($itemObject){
        $total = 0;
        $delay = 0;

        foreach ($itemObject as $item){

            $open_day = $this->delayQualification($item)['open_day'];

            if($open_day){

                $total++;
                $delay += $open_day;
            }
        }

        return ($total && $delay) ? round($delay / $total) : 0;
    }


    /**
     * @param $itemObject
     * @return float|int
     */
    protected function delayMediumTreatmentOpenDay($itemObject){
        $total = 0;
        $delay = 0;

        foreach ($itemObject as $item){

            $open_day = $this->delayTreatment($item)['open_day'];

            if($open_day){

                $total++;
                $delay += $open_day;
            }
        }

        return ($total && $delay) ? round($delay / $total) : 0;
    }


    /**
     * @param $itemObject
     * @return float|int
     */
    protected function delayMediumTreatmentWorkingDay($itemObject){
        $total = 0;
        $delay = 0;

        foreach ($itemObject as $item){

            $working_day = $this->delayTreatment($item)['working_day'];

            if($working_day){

                $total++;
                $delay += $working_day;
            }
        }

        return ($total && $delay) ? round($delay / $total) : 0;
    }


    /**
     * @param $itemObject
     * @return mixed
     */
    protected function totalTreated($itemObject){

        return $itemObject->filter(function ($item){

            return ($item->activeTreatment && $item->activeTreatment->validated_at);

        })->count();
    }


    /**
     * @param $itemObject
     * @return mixed
     */
    protected function totalNoValidated($itemObject){

        return $itemObject->filter(function ($item){

            return ($item->activeTreatment && !$item->activeTreatment->validated_at);

        })->count();
    }

    /**
     * @param $itemObject
     * @return mixed
     */
    protected function totalUnfounded($itemObject){

        return $itemObject->filter(function ($item){

            return ($item->activeTreatment && $item->activeTreatment->declared_unfounded_at);

        })->count();
    }





}
