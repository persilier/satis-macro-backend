<?php
namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;

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
            'claimObject.claimCategory',
            'claimer',
            'relationship',
            'accountTargeted',
            'institutionTargeted',
            'unitTargeted',
            'requestChannel',
            'responseChannel',
            'amountCurrency',
            'createdBy.identite',
            'completedBy.identite',
            'files',
            'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite',
            'activeTreatment.responsibleUnit',
            'activeTreatment.assignedToStaffBy.identite'
        ];

        return $relations;
    }
    /**
     * @return array
     */
    protected function rulePeriode(){

        $data = [

            'date_start' => 'required|date_format:Y-m-d',
            'date_end' => 'required|date_format:Y-m-d|after:date_start',
            'institution_id' => 'exists:institutions,id'
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

        if($request->has('institution_id')){

            $claims = $claims->where('institution_targeted_id', $request->institution_id);
        }

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

            $data = $this->tabDatas($claim, $myInstitution);

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

            $data = $this->tabDatas($claim, $myInstitution);

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

            $data = $this->tabDatas($claim, $myInstitution);

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
            function($item){
                return $item->claimObject->claimCategory->name;
            },
            function($item){
                return $item->claimObject->name;
            }
        ]);

        $claimCollection = collect([]);

        $claims = $claims->map(function ($categories, $keyInstitution) use ($claimCollection, $myInstitution){

            $categories->map(function ($objects, $keyCategory) use ($claimCollection, $myInstitution, $keyInstitution){

                $objects->map(function ($claims, $keyObject)  use ($claimCollection, $myInstitution,$keyInstitution, $keyCategory){

                    $data = [
                        'filiale' =>  $keyInstitution,
                        'claimCategorie' => $keyCategory,
                        'claimObject' => $keyObject,
                        'totalClaim' => (string) $claims->count(),
                        'totalTreated' => (string) $this->totalTreated($claims),
                        'totalUnfounded' => (string) $this->totalUnfounded($claims),
                        'totalNoValidated' => (string) $this->totalNoValidated($claims),
                        'delayMediumQualification' => (string) $this->delayMediumQualification($claims),
                        'delayPlanned' => (string) $claims->first()->claimObject->time_limit,
                        'delayMediumTreatmentWithWeekend' => (string)  $this->delayMediumTreatmentWithWeekend($claims),
                        'delayMediumTreatmentWithoutWeekend' => (string)  $this->delayMediumTreatmentWithoutWeekend($claims),
                        'percentageTreatedInDelay' => (string)  $this->percentageInTime($claims),
                        'percentageTreatedOutDelay' => (string)  $this->percentageOutTime($claims),
                        'percentageNoTreated' => (string) $this->percentageNotTreated($claims)
                    ];

                    if($myInstitution){

                        $data = Arr::except($data, 'filiale');

                    }
                    return $claimCollection->push($data);

                });

            });

      });

        return $claimCollection;

    }


    /**
     * @param $claim
     * @param $myInstitution
     * @return void
     */
    protected function tabDatas($claim, $myInstitution){

        $data =  [
            'filiale' => $claim->institutionTargeted->name,
            'typeClient' => $claim->accountType,
            'client' => $this->client($claim),
            'account' => $claim->accountTargeted ? $claim->accountTargeted->number : '',
            'telephone' => $this->telephone($claim),
            'agence' =>  $this->agence($claim),
            'claimCategorie' => $claim->claimObject->claimCategory->name,
            'claimObject' => $claim->claimObject->name,
            'requestChannel' => $claim->requestChannel->name,
            'commentClient' => $claim->description,
            'functionTreating' => ($claim->activeTreatment && $claim->activeTreatment->responsibleUnit) ? $claim->activeTreatment->responsibleUnit->name : null,
            'staffTreating' => $this->staffTreating($claim),
            'solution' => $this->solution($claim),
            'status' => $this->status($claim),
            'dateRegister' => $claim->created_at->copy()->format('d/m/y'),
            'dateQualification' => $this->dateQualification($claim),
            'dateTreatment' => $this->dateTreatment($claim),
            'dateClosing' => $this->closing($claim),
            'delayQualifWithWeekend' => (string) $this->delayQualification($claim)['withWeekend'],
            'delayTreatWithWeekend' => (string) $this->delayTreatment($claim)['withWeekend'],
            'delayTreatWithoutWeekend' => (string) $this->delayTreatment($claim)['withoutWeekend'],
            'amountDisputed' =>  $claim->amount_disputed,
            'accountCurrency' => $this->currency($claim)
        ];

        if($myInstitution){

            $data = Arr::except($data, 'filiale');
        }

        return $data;

    }

    public function agence($claim){

        return $claim->unitTargeted ? $claim->unitTargeted->name : ($claim->createdBy->unit ? $claim->createdBy->unit->name : '');
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

        $allStatus = [
            'incomplete' => 'Incomplet',
            'full' => 'Complet',
            'assigned_to_staff' => 'affecté à un staff',
            'archived' => 'Archivé',
            'transferred_to_targeted_institution' => "transféré à l'institution ciblée",
            'transferred_to_unit' => 'Transféré dans l\'unité concerné',
            'treated' => 'Traité',
            'rejected' => 'rejeté',
            'unfounded' => 'Non fondé',
            'validated' => 'Validé'
        ];

        $claim->status ? $status = $allStatus[$claim->status] : $status = '';

        return $status;
    }

    /**
     * @param $claim
     * @return string
     */
    protected function staffTreating($claim){

        $staff = '';

        ($claim->activeTreatment && $claim->activeTreatment->responsibleStaff) ?
            $staff .= $claim->activeTreatment->responsibleStaff->identite->firstname.' '.$claim->activeTreatment->responsibleStaff->identite->lastname : null;

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

        $withWeekend = null;
        $withoutWeekend = null;

        if($claim->activeTreatment && $claim->activeTreatment->transferred_to_unit_at){
            $withoutWeekend = $this->diffInWorkingDays($claim->created_at->copy(), $claim->activeTreatment->transferred_to_unit_at->copy());
            $withWeekend = $claim->created_at->copy()->diffInDays($claim->activeTreatment->transferred_to_unit_at->copy());
        }

        return [
            'withWeekend' => $withWeekend,
            'withoutWeekend' => $withoutWeekend
        ];
    }

    /**
     * @param $claim
     * @return |null
     */
    protected function delayTreatment($claim){

        $withWeekend = null;
        $withoutWeekend = null;

        if($claim->activeTreatment && $claim->activeTreatment->validated_at){

            $withWeekend = $claim->created_at->copy()->diffInDays(($claim->activeTreatment->validated_at->copy()));
            $withoutWeekend = $this->diffInWorkingDays($claim->created_at->copy(), $claim->activeTreatment->validated_at->copy());
        }

        return [
            'withWeekend' => $withWeekend,
            'withoutWeekend' => $withoutWeekend
        ];
    }


    /**
     * @param $start
     * @param $end
     * @return mixed
     */
    protected function diffInWorkingDays($start, $end){

        $weekendDays = $start->diffInWeekendDays($end);

        return ($start->diffInDays($end) - $weekendDays);

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

            $withWeekend = $this->delayQualification($item)['withWeekend'];

            if($withWeekend){

                $total++;
                $delay += $withWeekend;
            }
        }

        return ($total && $delay) ? round($delay / $total) : 0;
    }


    /**
     * @param $itemObject
     * @return float|int
     */
    protected function delayMediumTreatmentWithWeekend($itemObject){
        $total = 0;
        $delay = 0;

        foreach ($itemObject as $item){

            $withWeekend = $this->delayTreatment($item)['withWeekend'];

            if($withWeekend){

                $total++;
                $delay += $withWeekend;
            }
        }

        return ($total && $delay) ? round($delay / $total) : 0;
    }


    /**
     * @param $itemObject
     * @return float|int
     */
    protected function delayMediumTreatmentWithoutWeekend($itemObject){
        $total = 0;
        $delay = 0;

        foreach ($itemObject as $item){

            $withoutWeekend = $this->delayTreatment($item)['withoutWeekend'];

            if($withoutWeekend){

                $total++;
                $delay += $withoutWeekend;
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
