<?php
namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\UnitCanBeTargetRules;
use Satis2020\ServicePackage\Rules\UnitCanTreatRules;

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
            'activeTreatment.assignedToStaffBy.identite',
            'activeTreatment.staffTransferredToUnitBy.identite'
        ];

        return $relations;
    }
    /**
     * @return array
     */
    protected function rulePeriode(){

        $data = [

            'date_start' => 'required|date_format:Y-m-d',
            'date_end' => 'required|date_format:Y-m-d|after_or_equal:date_start',
            'institution_id' => 'exists:institutions,id'
        ];

        return $data;

    }


    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return array
     */
    protected function ruleFilter($request, $with_client = true, $with_relationship = false, $with_unit = true){

        $data = $this->rulePeriode();

        $data['claim_category_id'] = 'nullable|exists:claim_categories,id';
        $data['claim_object_id'] = 'nullable|exists:claim_objects,id';
        $data['request_channel_slug'] = 'nullable|exists:channels,slug';

        if ($with_relationship) {
            $data['relationship_id'] = 'exists:relationships,id';
        }

        if($with_unit){
            $data['unit_targeted_id'] = ['nullable', 'exists:units,id', new UnitBelongsToInstitutionRules($request->institution_targeted_id), new UnitCanBeTargetRules];
        }

        if($with_client){
            $data['account_type_id'] = 'exists:type_clients,id';
        }

        if($with_relationship){
            $data['responsible_unit_id'] = ['nullable', 'exists:units,id', new UnitCanTreatRules];
        }else{
            $data['responsible_unit_id'] = ['nullable', 'exists:units,id', new UnitBelongsToInstitutionRules($request->institution_targeted_id), new UnitCanTreatRules];
        }

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
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return mixed
     */
    protected function getAllClaimByPeriode($request, $myInstitution = false , $with_client = true, $with_relationship = false, $with_unit = true){

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

        if($request->has('claim_category_id') || $request->has('claim_object_id')){


            if($request->has('claim_object_id')){

                $claims = $claims->where('claim_object_id', $request->claim_object_id);

            }else{

                $claims = $claims->whereHas('claimObject', function ($q) use ($request){

                    $q->where('claim_category_id', $request->claim_category_id);

                });
            }

        }


        if($request->has('request_channel_slug')){

            $claims = $claims->where('request_channel_slug', $request->request_channel_slug);
        }

        if($with_unit){

            if($request->has('unit_targeted_id')){

                $claims = $claims->where('unit_targeted_id', $request->unit_targeted_id);
            }
        }



        if($request->has('responsible_unit_id')){

            $claims = $claims->whereHas('activeTreatment', function ($r) use ($request){

                $r->where('responsible_unit_id', $request->responsible_unit_id);

            });
        }

        if($with_client){

            if($request->has('account_type_id')){

                $claims = $claims->whereHas('accountTargeted', function ($r) use ($request){

                    $r->where('account_type_id', $request->account_type_id);

                });
            }
        }

        if($with_relationship){

            if($request->has('relationship_id')){

                $claims = $claims->whereHas('relationship', function ($o) use ($request){

                    $o->where('id', $request->relationship_id);

                });
            }
        }

        if($request->has('status')){

            $claims = $claims->where('status', $request->status);
        }

        if($request->has('type_client')){

            $claims = $claims->whereHas('claimer', function ($o) use ($request){

                $o->where('type_client', $request->type_client);

            });
        }


        return $claims;

    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return \Illuminate\Support\Collection
     */
    protected function resultatsGlobalState($request, $myInstitution = false, $with_client = true, $with_relationship = false, $with_unit = true, $for_excel=false){

        $datas = collect([]);

        $claims = $this->getAllClaimByPeriode($request, $myInstitution, $with_client, $with_relationship, $with_unit)->get();

        foreach ($claims as $claim){

            $data = $this->tabDatas($claim, $myInstitution, $with_relationship, $for_excel);

            $datas->push($data);
        }

        return $datas;
    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return \Illuminate\Support\Collection
     */
    protected function resultatsStateMore30Days($request, $myInstitution = false, $with_client = true, $with_relationship = false, $with_unit = true,  $for_excel=false){

        $datas = collect([]);

        $claims = $this->getAllClaimByPeriode($request, $myInstitution, $with_client, $with_relationship, $with_unit)->get()->filter(function ($item){

            return ($item->created_at->copy()->diffInDays(now()) >= 30) && ($item->activeTreatment && $item->activeTreatment->validated_at);

        })->values();

        foreach ($claims as $claim){

            $data = $this->tabDatas($claim, $myInstitution, $with_relationship, $for_excel);

            $datas->push($data);
        }

        return $datas;

    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return \Illuminate\Support\Collection
     */
    protected function resultatsStateOutTime($request, $myInstitution = false, $with_client = true, $with_relationship = false, $with_unit = true){

        $datas = collect([]);

        $claims = $this->getAllClaimByPeriode($request, $myInstitution, $with_client, $with_relationship, $with_unit)->get()->filter(function ($item){

            return ($item->created_at->copy()->diffInDays(now()) > $item->time_limit) && ($item->activeTreatment && !$item->activeTreatment->validated_at);

        })->values();

        foreach ($claims as $claim){

            $data = $this->tabDatas($claim, $myInstitution, $with_relationship);

            $datas->push($data);
        }

        return $datas;

    }


    /**
     * @param $request
     * @param bool $myInstitution
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return array
     */


    protected function resultatsStateAnalytique($request, $myInstitution = false, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        $claims = $this->getAllClaimByPeriode($request, $myInstitution, $with_client, $with_relationship, $with_unit)->get()->groupBy([
            function ($item) {
                return $item->institutionTargeted->name;
            },
            function($item){
                return optional(optional($item->claimObject)->claimCategory)->name;
            },
            function($item){
                return optional($item->claimObject)->name;
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
                        'delayPlanned' => (string) optional($claims->first()->claimObject)->time_limit,
                        'delayMediumTreatmentWithWeekend' => (string)  $this->delayMediumTreatmentWithWeekend($claims),
                        'delayMediumTreatmentWithoutWeekend' => (string)  $this->delayMediumTreatmentWithoutWeekend($claims),
                        'percentageTreatedInDelay' => (string)  $this->percentageInTime($claims),
                        'percentageTreatedOutDelay' => (string)  $this->percentageOutTime($claims),
                        'percentageNoTreated' => (string) $this->percentageNotTreated($claims),
                        'countTreatedInDelay' => (string)  $this->countInTime($claims),
                        'countTreatedOutDelay' => (string)  $this->countOutTime($claims),
                        'countNoTreated' => (string) $this->countNotTreated($claims)

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
     * @param $with_relationship
     * @return array
     */
    protected function tabDatas($claim, $myInstitution, $with_relationship, $for_excel = false){

        $data =  [
            'filiale' => $claim->institutionTargeted->name,
            'relationShip' => $this->relationShip($claim),
            'typeClient' => $claim->accountType,
            'client' => $this->client($claim),
            'reference' => $claim->reference,
            'account' => $claim->accountTargeted ? $claim->accountTargeted->number : $claim['account_number'],
            'telephone' => $this->telephone($claim),
            'reference' => $claim->reference,
            'agence' =>  $this->agence($claim),
            'claimCategorie' => optional( optional($claim->claimObject)->claimCategory)->name,
            'claimObject' => optional($claim->claimObject)->name,
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
            'accountCurrency' => $this->currency($claim),
            'collector' => $claim->createdBy!= null ? $claim->createdBy->identite : null,
            'unit' => ($claim->unitTargeted) ? $claim->unitTargeted : null,
            'pilot_in_charge' => ($claim->activeTreatment && $claim->activeTreatment->staffTransferredToUnitBy) ?  $claim->activeTreatment->staffTransferredToUnitBy->identite : null,
            'collector_info' =>  $claim->createdBy!= null ? $claim->createdBy->identite->firstname ." ". $claim->createdBy->identite->lastname : "",
            'unit_info' => ($claim->unitTargeted) ? $claim->unitTargeted->name : "",
            'pilot_in_charge_info' => ($claim->activeTreatment && $claim->activeTreatment->staffTransferredToUnitBy) ?  $claim->activeTreatment->staffTransferredToUnitBy->identite->firstname. " ". $claim->activeTreatment->staffTransferredToUnitBy->identite->lastname : "",
        ];

        if ($for_excel){
            unset($data["collector"]);
            unset($data["unit"]);
            unset($data["pilot_in_charge"]);
        }

        if($myInstitution){

            $data = Arr::except($data, 'filiale');
        }

        if($with_relationship){

            $data = Arr::except($data, 'typeClient');
            $data = Arr::except($data, 'client');
            $data = Arr::except($data, 'account');

        }else{

            $data = Arr::except($data, 'relationShip');
        }

        return $data;

    }

    /**
     * @param $claim
     * @return string
     */
    public function agence($claim){

        return optional($claim->unitTargeted)->name ?: optional(optional($claim->createdBy)->unit)->name;
    }


    /**
     * @param $claim
     * @return string
     */
    public function relationShip($claim){

        return $claim->relationShip ? $claim->relationShip->name  : '';
    }

    /**
     * @param $claim
     * @return string|null
     */
    protected function client($claim){

        $client = null;

        if ($claim->claimer) {

            if ($claim->claimer->raison_sociale != null) {
                $client = $claim->claimer->raison_sociale;
            } else {
                $client = $claim->claimer->firstname.' '.$claim->claimer->lastname ;
            }
            
        } 
        
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

        $allStatus = $this->allStatus();

        $claim->status ? $status = $allStatus[$claim->status] : $status = '';

        return $status;
    }


    /**
     * @return array
     */
    protected function allStatus(){

        return [
            'incomplete' => 'Incomplet',
            'full' => 'Complet',
            'assigned_to_staff' => 'affecté à un staff',
            'archived' => 'Archivé',
            'transferred_to_targeted_institution' => "transféré à l'institution ciblée",
            'transferred_to_unit' => 'Transféré dans l\'unité concerné',
            'treated' => 'Traité',
            'rejected' => 'rejeté',
            'unfounded' => 'Non fondé',
            'validated' => 'Validé',
            'unsatisfied' => 'Insatisfait',
            'closed' => 'Clôturé'
        ];
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
    protected function countNotTreated($itemObject){

        return $this->totalNoValidated($itemObject);
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
    protected function countOutTime($itemObject){

        return $this->totalTreatedOutDelay($itemObject);
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
     * @return float|int
     */
    protected function countInTime($itemObject){
        return  $this->totalTreatedInDelay($itemObject);
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
     * @param int $timelimit
     * @return mixed
     */
    protected function totalTreatedOutRegulatoryDelay($itemObject,$timelimit){

        return $itemObject->filter(function ($item) use($timelimit){

            return ($item->activeTreatment && $item->activeTreatment->validated_at && ($item->created_at->copy()->addWeekdays($timelimit) > $item->activeTreatment->validated_at));

        })->count();
    }

    /**
     * @param $claims
     * @return mixed
     */
    protected function totalOutDelay($claims){

        return $claims->filter(function ($item){
            return ($item->created_at->copy()->addWeekdays($item->time_limit) < now());
        })->count();
    }

    protected function getRegulatoryLimit()
    {
        return (int)json_decode(Metadata::query()->where('name', Metadata::REGULATORY_LIMIT)->firstOrFail()->data);
    }

    /**
     * @param $claims
     * @return mixed
     */
    protected function totalOutRegulatoryDelay($claims,$timelimit){

        return $claims->filter(function ($item) use($timelimit){
            return ($item->created_at->copy()->addWeekdays($timelimit) < now());
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
     * @param $claims
     * @return mixed
     */
    protected function claimTreated($claims){

        return $claims->filter(function ($item){

            return ($item->activeTreatment && $item->activeTreatment->validated_at);

        });
    }

    /**
     * @param $claims
     * @return mixed
     */
    protected function claimsNotTreated($claims){

        return $claims->filter(function ($item){

            return (!$item->activeTreatment || !$item->activeTreatment->validated_at);
        });
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


    /**
     * @param $institution
     * @return string
     */
    protected function logo($institution){

        if(is_null($institution->logo)){

            $logo = asset('assets/reporting/images/satisLogo.png');

        }else{

            $logo = $institution->logo;
        }

        return $logo;
    }


    /**
     * @return string
     */
    protected function colorTableHeader(){

        return "#7F9CF5";
    }


}
