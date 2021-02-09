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

        return $claim->status === "archived" ?
            ($claim->activeTreatment->satisfaction_measured_at ?
                $claim->activeTreatment->satisfaction_measured_at->copy()->format('d/m/y') :
                $claim->activeTreatment->validated_at->copy()->format('d/m/y'))   : null;
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





}
