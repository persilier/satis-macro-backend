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

            $data = [
                'typeClient' => $claim->accountType,
                'client' => $claim->claimer ? $claim->claimer->firstname.' '.$claim->claimer->lastname : '',
                'account' => $claim->accountTargeted ? $claim->accountTargeted->number : '',
                'agence' =>  $claim->unitTargeted ? $claim->unitTargeted->name : '',
                'claimCategorie' => $claim->claimObject->claimCategory->name,
                'claimObject' => $claim->claimObject->name,
                'requestChannel' => $claim->requestChannel->name,
                'commentClient' => $claim->description,
                'staffTreating' => $this->staffTreating($claim),
                'solution' => $this->solution($claim),
                'status' => $this->status($claim),
                'dateRegister' => $claim->created_at->copy()->format('d/m/y'),
                'dateTreatment' => $this->dateTreatment($claim),
                'dateQualification' => $this->dateQualification($claim),
                'dateClosing' => $this->closing($claim),
                'delayQualification' => $this->delayQualification($claim),
                'delayTreatment' => $this->delayTreatment($claim),
                'amountDisputed' =>  $claim->amount_disputed,
                'accountCurrency' => $claim->amountCurrency ? $claim->amountCurrency->name : ''
            ];

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
    protected function resultatsHorsDelayState($request, $myInstitution = false){

        $datas = collect([]);

        $claims = $this->getAllClaimByPeriode($request, $myInstitution)->get()->filter(function ($item){

            $datePrev = $item->created_at->addDays($item->time_limit);

            if($item->activeTreatment){

                //return $item ? : false;
            }

        })->values();

        foreach ($claims as $claim){

            $data = [
                'typeClient' => $claim->accountType,
                'client' => $claim->claimer ? $claim->claimer->firstname.' '.$claim->claimer->lastname : '',
                'account' => $claim->accountTargeted ? $claim->accountTargeted->number : '',
                'agence' =>  $claim->unitTargeted ? $claim->unitTargeted->name : '',
                'claimCategorie' => $claim->claimObject->claimCategory->name,
                'claimObject' => $claim->claimObject->name,
                'requestChannel' => $claim->requestChannel->name,
                'commentClient' => $claim->description,
                'staffTreating' => $this->staffTreating($claim),
                'solution' => $this->solution($claim),
                'status' => $this->status($claim),
                'dateRegister' => $claim->created_at->copy()->format('d/m/y'),
                'dateTreatment' => $this->dateTreatment($claim),
                'dateQualification' => $this->dateQualification($claim),
                'dateClosing' => $this->closing($claim),
                'delayQualification' => $this->delayQualification($claim),
                'delayTreatment' => $this->delayTreatment($claim),
                'amountDisputed' =>  $claim->amount_disputed,
                'accountCurrency' => $claim->amountCurrency ? $claim->amountCurrency->name : ''
            ];

            if(!$myInstitution){

                $data = Arr::prepend($data, $claim->institutionTargeted->name, 'filiale');
            }

            $datas->push($data);
        }

        return $datas;

    }





    /**
     * @param $claim
     * @return string
     */
    protected function dateQualification($claim){

        return ($claim->activeTreatment && $claim->activeTreatment->transferred_to_unit_at) ? $claim->activeTreatment->transferred_to_unit_at->copy()->format('d/m/y') : '';
    }

    /**
     * @param $claim
     * @return string
     */
    protected function dateTreatment($claim){

        return ($claim->activeTreatment && $claim->activeTreatment->solved_at) ? $claim->activeTreatment->solved_at->copy()->format('d/m/y') : '';
    }


    /**
     * @param $claim
     * @return string
     */
    protected function status($claim){

        return $claim->status ? $claim->status : '';
    }

    /**
     * @param $claim
     * @return string
     */
    protected function staffTreating($claim){

        $staff = '';

        ($claim->activeTreatment && $claim->activeTreatment->responsibleUnit) ? $staff .= $claim->activeTreatment->responsibleUnit->name : '';

        ($claim->activeTreatment && $claim->activeTreatment->responsibleStaff) ? $staff .= ' - ' .$claim->activeTreatment->responsibleStaff->identite->firstname.' '.$claim->activeTreatment->responsibleStaff->identite->lastname : '';

        return $staff;
    }

    /**
     * @param $claim
     * @return string
     */
    protected function solution($claim){

        return ($claim->activeTreatment && $claim->activeTreatment->validated_at) ? $claim->activeTreatment->solution : '';
    }

    /**
     * @param $claim
     * @return string
     */
    protected function closing($claim){

        return $claim->status === "archived" ? $claim->activeTreatment->satisfaction_measured_at->copy()->format('d/m/y')  : '';
    }


    /**
     * @param $claim
     * @return |null
     */
    protected function delayQualification($claim){

        $delay = null;

        ($claim->activeTreatment && $claim->activeTreatment->transferred_to_unit_at) ? $claim->created_at->copy()->diffInDays(($claim->activeTreatment->transferred_to_unit_at), false) : '';

        return $delay;
    }

    /**
     * @param $claim
     * @return |null
     */
    protected function delayTreatment($claim){

        $delay = null;

        ($claim->activeTreatment && $claim->activeTreatment->solved_at) ? $claim->activeTreatment->transferred_to_unit_at->copy()->diffInDays(($claim->activeTreatment->solved_at), false) : '';

        return $delay;
    }


}
