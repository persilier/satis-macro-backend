<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;


/**
 * Trait ReportingClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait ReportingClaim
{

    /**
     * @return Collection|ClaimCategory[]
     */
    protected function getAllCategoryClaim()
    {
        $categories =  ClaimCategory::all();

        return $categories;

    }


    /**
     * @param $request
     * @param bool $institutionId
     * @return Collection|\Illuminate\Support\Collection|ClaimCategory[]
     */
    protected function numberClaimByObject($request, $institutionId = false)
    {

        $categories = $this->getAllCategoryClaim()->map(function ($item) use ($request, $institutionId) {
            $item['claim_objects'] = $this->numberClaimObject($request, $item->id, $institutionId);
            return $item;
        });

        return $categories;

    }


    /**
     * @param $request
     * @param $institutionId
     * @return Collection|Claim[]
     */
    protected function queryNumberObject($request, $institutionId){

        if($request->has('date_start') && $request->has('date_end')){

            if($institutionId){
                $claims = Claim::where('institution_targeted_id', $institutionId)
                    ->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                    ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay())
                    ->get();
            }else{
                $claims = Claim::whereBetween('created_at', [$request->date_end, $request->date_start])->get();
            }

        }else{
            if($institutionId){
                $claims = Claim::where('institution_targeted_id', $institutionId)->get();
            }else{
                $claims = Claim::all();
            }
        }
        return $claims;

    }

    /**
     * @param $request
     * @param $claimCategoryId
     * @param bool $institutionId
     * @return mixed
     */
    protected function numberClaimObject($request, $claimCategoryId, $institutionId = false){

        $claims = $this->queryNumberObject($request, $institutionId);

        $objects = ClaimObject::where('claim_category_id', $claimCategoryId)->get()->map(function ($item) use ($claims){
            $claimsResolue = $this->statistique($claims, $item->id, 'archived');
            $item['total'] = $this->statistique($claims, $item->id, false, true);
            $item['incomplete'] = $this->statistique($claims, $item->id, 'incomplete');
            $item['toAssignementToUnit'] = $this->statistique($claims, $item->id, 'transferred_to_targeted_institution');
            $item['toAssignementToStaff'] = $this->statistique($claims, $item->id, 'transferred_to_unit');
            $item['awaitingTreatment'] = $this->statistique($claims, $item->id, 'assigned_to_staff');
            $item['toValidate'] = $this->statistique($claims, $item->id, 'treated');
            $item['toMeasureSatisfaction'] = $this->statistique($claims, $item->id, 'validated');
            $item['percentage'] = (($claimsResolue !== 0) && ($item['total']!==0)) ? round((($claimsResolue/$item['total']) * 100),2) : 0;
            return $item;
        });

        return $objects;
    }


    /**
     * @param $claims
     * @param $objectClaimId
     * @param $status
     * @param bool $total
     * @return mixed
     */
    protected function statistique($claims, $objectClaimId, $status, $total = false){

        if($total){

            return $claims = $claims->filter(function ($item) use ($objectClaimId){
                return $item->claim_object_id === $objectClaimId;
            })->count();

        }else{

            switch ($status){
                case 'transferred_to_targeted_institution':

                    return $claims->filter(function ($item) use ($objectClaimId , $status){
                        return (($item->claim_object_id === $objectClaimId) && (($item->status === 'full') || ($item->status === $status)));
                    })->count();

                case 'archived':

                    return $claims->filter(function ($item) use ($objectClaimId , $status){
                        return (($item->claim_object_id === $objectClaimId) && (($item->status === 'validated') || ($item->status === $status)));
                    })->count();

                default:

                    return $claims->filter(function ($item) use ($objectClaimId , $status){
                        return (($item->claim_object_id === $objectClaimId) && ($item->status === $status));
                    })->count();
            }

        }

    }

    /**
     * @param $request
     * @param bool $institutionId
     * @return Collection|Channel[]
     */
    protected function numberChannels($request, $institutionId =  false){

        $channels = Channel::all();

        $channels->map(function ($item) use ($request,$institutionId){
            $result = $this->countClaimByChannel($request,$item->slug, $institutionId);

            $item['total_claim'] = $result['total_claim'];
            $item['pourcentage'] = $result['pourcentage'];

            return $item;
        });

        return $channels;
    }


    protected function queryCountClaimByChannel($request, $slug, $institutionId){

        if($request->has('date_start') && $request->has('date_end')){

            $claims = Claim::where('request_channel_slug', $slug)
                ->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay());

        }else{
            $claims = Claim::where('request_channel_slug', $slug);
        }

        if($institutionId){
            $claims->where('institution_targeted_id', $institutionId);
        }

        return $claims;
    }

    /**
     * @param $request
     * @param $slug
     * @param bool $institutionId
     * @return mixed
     */
    protected function countClaimByChannel($request,$slug, $institutionId){

        $claims = $this->queryCountClaimByChannel($request, $slug, $institutionId);

        $nbre =  $claims->get()->count();

        $total = $this->countClaimWithoutChannel($request, $institutionId);
        return [
            'total_claim' => $nbre,
            'pourcentage' => (($nbre !== 0) && ($total!==0)) ? round((($nbre/$total) * 100),2) : 0
        ];
    }


    /**
     * @param $request
     * @param bool $institutionId
     * @return int
     */
    protected function countClaimWithoutChannel($request, $institutionId = false){

        if($request->has('date_start') && $request->has('date_end')){

            if(!$institutionId){
                $nbre = Claim::where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                    ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay())
                    ->get()->count();
            }else{
                $nbre =  Claim::where('institution_targeted_id', $institutionId)
                    ->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                    ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay())->get()->count();
            }

        }else{

            if(!$institutionId){
                $nbre = Claim::all()->count();
            }else{
                $nbre =  Claim::where('institution_targeted_id', $institutionId)->get()->count();
            }
        }

        return $nbre;
    }



    /**
     * @param $request
     * @param bool $institutionId
     * @return array
     */
    protected function qualificationPeriod($request, $institutionId = false){

        $totalCalim = $this->numberClaimByPeriod($request, $institutionId)->count();

        $datas = $this->numberClaimByPeriod($request, $institutionId);

        return [
            '0-2' => $this->countFilterBetweenDatePeriod($datas, 0, 2, $finish = false, $totalCalim),
            '2-4' => $this->countFilterBetweenDatePeriod($datas, 2, 4, $finish = false, $totalCalim),
            '4-6' => $this->countFilterBetweenDatePeriod($datas, 4, 6, $finish = false, $totalCalim),
            '6-10' => $this->countFilterBetweenDatePeriod($datas, 6, 10, $finish = false, $totalCalim),
            '+10' => $this->countFilterBetweenDatePeriod($datas, false, 10, $finish = true, $totalCalim),
        ];

    }


    /**
     * @param $request
     * @param bool $institutionId
     * @return array
     */
    protected function treatmentPeriod($request, $institutionId = false){

        $totalClaim = $this->numberClaimByTreatmentPeriod($request,$institutionId)->count();

        $datas = $this->numberClaimByTreatmentPeriod($request,$institutionId);

        return [
            '0-2' => $this->countFilterBetweenDateTreatmentPeriod($datas, 0, 2, $finish = false, $totalClaim),
            '2-4' => $this->countFilterBetweenDateTreatmentPeriod($datas, 2, 4, $finish = false, $totalClaim),
            '4-6' => $this->countFilterBetweenDateTreatmentPeriod($datas, 4, 6, $finish = false, $totalClaim),
            '6-10' => $this->countFilterBetweenDateTreatmentPeriod($datas, 6, 10, $finish = false, $totalClaim),
            '+10' => $this->countFilterBetweenDateTreatmentPeriod($datas, false, 10, $finish = true, $totalClaim),
        ];

    }


    /**
     * @param $datas
     * @param $valStart
     * @param $valEnd
     * @param bool $finish
     * @param $total
     * @return mixed
     */
    protected  function countFilterBetweenDatePeriod($datas, $valStart, $valEnd, $finish, $total){

        $data['total'] = $datas->filter(function ($item) use ($valStart, $valEnd, $finish){
            $nbre = $item->activeTreatment->assigned_to_staff_at->diffInDays($item->completed_at, true);
            if($finish){
               return (($nbre >= $valStart) || ($nbre < $valEnd)) ;
            }else{
                return ($nbre > $valEnd);
            }
        })->count();

        $data['pourcentage'] = (($data['total'] !== 0) && ($total !==0)) ? round((( $data['total']/$total) * 100),2) : 0;

        return $data;
    }


    /**
     * @param $datas
     * @param $valStart
     * @param $valEnd
     * @param bool $finish
     * @param $total
     * @return mixed
     */
    protected  function countFilterBetweenDateTreatmentPeriod($datas, $valStart, $valEnd, $finish, $total){

        $data['total'] = $datas->filter(function ($item) use ($valStart, $valEnd, $finish){
            $nbre = $item->activeTreatment->validated_at->diffInDays($item->activeTreatment->assigned_to_staff_at, true);
            if($finish){
                return (($nbre >= $valStart) || ($nbre < $valEnd)) ;
            }else{
                return ($nbre > $valEnd);
            }
        })->count();

        $data['pourcentage'] = ( ($data['total'] !== 0) && ($total !==0)) ? round((( $data['total']/$total) * 100),2) : 0;

        return $data;
    }

    /**
     * @param $request
     * @param $institutionId
     * @param string $condition
     * @param string $orderBy
     * @return Builder[]|Collection
     */
    protected  function numberClaimByPeriod($request, $institutionId, $condition = 'assigned_to_staff_at', $orderBy = 'completed_at'){

        $claims = $this-> queryClaimByPeriod($institutionId, $orderBy);

        if($request->has('date_start') && $request->has('date_end')){
            $claims->where('claims.created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('claims.created_at', '<=',Carbon::parse($request->date_end)->endOfDay());
        }

        return $claims->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id');
        })->where('treatments.'.$condition, '!=', null)->select('claims.*')->get();

    }


    /**
     * @param $institutionId
     * @param $orderBy
     * @return Builder
     */
    protected function queryClaimByPeriod($institutionId, $orderBy){
        if(!$institutionId){
            $claims = Claim::with('activeTreatment')->orderBy($orderBy, 'ASC');
        }else{
            $claims =  Claim::with('activeTreatment')->orderBy($orderBy, 'ASC')->where('institution_targeted_id', $institutionId);
        }

        return $claims;
    }


    /**
     * @param $request
     * @param $institutionId
     * @param string $condition
     * @param string $orderBy
     * @return Builder[]|Collection
     */
    protected  function numberClaimByTreatmentPeriod($request, $institutionId, $condition = 'validated_at', $orderBy = 'assigned_to_staff_at'){

        $claims = $this->queryClaimByTreatmentPeriod($institutionId);

        if($request->has('date_start') && $request->has('date_end')){
            $claims->where('claims.created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('claims.created_at', '<=',Carbon::parse($request->date_end)->endOfDay());
        }

        return $claims->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id');
        })->orderBy('treatments.'.$orderBy)->where('treatments.'.$condition, '!=', null)->select('claims.*')->get();

    }

    /**
     * @param $institutionId
     * @return Builder
     */
    protected function queryClaimByTreatmentPeriod($institutionId){

        if(!$institutionId){
            $claims = Claim::with('activeTreatment');
        }else{
            $claims =  Claim::with('activeTreatment')->where('institution_targeted_id', $institutionId);
        }

        return $claims;
    }


    protected function queryClaimByDayOrMonthOrYear($request, $institutionId){

        if($request->has('date_start') && $request->has('date_end')){

            $claims = Claim::where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay());

        }else{
            $claims = New Claim;
        }

        if($institutionId){
            $claims->where('institution_targeted_id', $institutionId);
        }

        return $claims;
    }


    protected function queryClaimByDayOrMonthOrYearResolue($request, $institutionId){

        $claims = $this->queryClaimByTreatmentPeriod($institutionId);

        if($request->has('date_start') && $request->has('date_end')){
            $claims->where('claims.created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('claims.created_at', '<=',Carbon::parse($request->date_end)->endOfDay());
        }

        return $claims->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id');
        })->where('claims.status',  'validated')->orWhere('claims.status','archived')->select('claims.*');

    }


    /**
     * @param $request
     * @param $institutionId
     * @return mixed
     */
    protected  function numberClaimByDayOrMonthOrYear($request, $institutionId){

        $claims_requests = $this->queryClaimByDayOrMonthOrYear($request, $institutionId)->get();

        $claims_resolues = $this->queryClaimByDayOrMonthOrYearResolue($request, $institutionId)->get();

        $date_start = Carbon::parse($request->date_start)->startOfDay();
        $date_end = Carbon::parse($request->date_end)->endOfDay();

        $results['months']['claims_received'] =  $this->rangerDate($claims_requests, $date_start, $date_end, '1 month');
        $results['months']['claims_resolved'] =  $this->rangerDate($claims_resolues, $date_start, $date_end, '1 month');

        $results['weeks']['claims_received'] =  $this->rangerDate($claims_requests, $date_start, $date_end, '1 week');
        $results['weeks']['claims_resolved'] =  $this->rangerDate($claims_resolues, $date_start, $date_end, '1 week');

        $results['days']['claims_received'] =  $this->rangerDate($claims_requests, $date_start, $date_end, '1 day');
        $results['days']['claims_resolved'] =  $this->rangerDate($claims_resolues, $date_start, $date_end, '1 day');

        return $results;

    }

    /**
     * @param $period
     * @param $value
     * @return string
     */
    protected function formatRangerDatePeriod($period, $value){
        if($period === '1 month'){
            $d = $value->format('m/y');
        }

        if($period === '1 week'){
            $d = $value->format('d/m/y').' - '.$value->endOfWeek()->format('d/m/y');
        }

        if($period === '1 day'){
            $d = $value->format('d/m/y').' - '.$value->endOfDay()->format('d/m/y');
        }

        return $d;
    }


    /**
     * @param $claims
     * @param $date_start
     * @param $date_end
     * @param $period
     * @return mixed
     */
    protected function rangerDate($claims, $date_start, $date_end, $period){

        $ranger = CarbonPeriod::create($date_start, $period, $date_end);

        foreach ($ranger as  $value){

            $d = $this->formatRangerDatePeriod($period, $value);

            if($period === '1 month'){
                $nbre[$d] = $this->grapheMonths($claims, $value, $period);
            }

            if($period === '1 week'){

                $nbre[$d] = $this->grapheMonths($claims, $value, $period);
            }

            if($period === '1 day'){

                $nbre[$d] = $this->grapheMonths($claims, $value, $period);
            }

        }

        return $nbre;
    }


    /**
     * @param $claims
     * @param $value
     * @param $period
     * @return mixed
     */
    protected function grapheMonths($claims, $value, $period){

        return $claims->filter(function ($item) use ($value, $period){

            if($period === '1 month'){
                return $item->created_at >= $value->startOfMonth() && $item->created_at <= $value->endOfMonth();
            }

            if($period === '1 week'){
                return $item->created_at >= $value->startOfWeek() && $item->created_at <= $value->endOfWeek();
            }

            if($period === '1 day'){
                return $item->created_at >= $value->startOfDay() && $item->created_at <= $value->endOfDay();
            }

        })->count();
    }


    /**
     * @param bool $institution
     * @return array
     */
    protected function rules($institution = true)
    {

        $data = [
            'date_start' => 'date_format:Y-m-d',
            'date_end' => 'date_format:Y-m-d|after:date_start'
        ];

        if($institution){
            $data['institution_id'] = 'sometimes|exists:institutions,id';
        }

        return $data;
    }




}