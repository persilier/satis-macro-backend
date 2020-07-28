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
use Satis2020\ServicePackage\Models\Institution;


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
                $claims = Claim::where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                    ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay())->get();
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
    protected function numberClaimObject($request, $claimCategoryId, $institutionId){

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
                        return (($item->claim_object_id === $objectClaimId) && (($item->status === 'unfounded') || ($item->status === 'validated') || ($item->status === $status)));
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


    /**
     * @param $request
     * @param $slug
     * @param $institutionId
     * @return mixed
     */
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
        })->where('claims.status',  'validated')->orWhere('claims.status','unfounded')->orWhere('claims.status','archived')->select('claims.*');

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

        $results['months']['claims_received'] =  $this->rangerDate($claims_requests, $this->rangerPerMonths($date_start, $date_end));
        $results['months']['claims_resolved'] =  $this->rangerDate($claims_resolues, $this->rangerPerMonths($date_start, $date_end));

        $results['weeks']['claims_received'] =  $this->rangerDate($claims_requests, $this->rangerPerWeeks($date_start, $date_end));
        $results['weeks']['claims_resolved'] =  $this->rangerDate($claims_resolues, $this->rangerPerWeeks($date_start, $date_end));

        $results['days']['claims_received'] =  $this->rangerDate($claims_requests, $this->rangerPerDays($date_start, $date_end));
        $results['days']['claims_resolved'] =  $this->rangerDate($claims_resolues, $this->rangerPerDays($date_start, $date_end));

        return $results;

    }

    /**
     * @param $claims
     * @param $ranger
     * @return mixed
     */
    protected function rangerDate($claims, $ranger){

        foreach ($ranger as  $value){

             $nbre[$value['text']] = $this->graphes($claims, $value);

        }

        return $nbre;
    }


    /**
     * @param $claims
     * @param $value
     * @return mixed
     */
    protected function graphes($claims, $value){

        return $claims->filter(function ($item) use ($value){

            if($item->created_at >= $value['period_start'] && $item->created_at <= $value['period_end'])
                return $item;

        })->count();
    }

    /**
     * @param $nbreDay
     * @param $date_start
     * @param $date_end
     * @return array
     */
    protected function rangerPerDays($date_start, $date_end){

        $nbreDays = $date_start->copy()->startOfDay()->diffInDays($date_end->copy()->endOfDay());

        $rangerDays = [];

        for($n = 0; $n <= $nbreDays; $n++){

            $rangerDays[$n]['text'] = $date_start->copy()->startOfDay()->addDays($n)->format('Y-m-d');
            $rangerDays[$n]['period_start'] = $date_start->copy()->startOfDay()->addDays($n);
            $rangerDays[$n]['period_end'] = $date_start->copy()->endOfDay()->addDays($n);

        }

        return $rangerDays;
    }

    /**
     * @param $date_start
     * @param $date_end
     * @return array
     */
    protected function rangerPerWeeks($date_start, $date_end){

        $diffFirstDayWeek = $date_start->copy()->startOfDay()->diffInDays($date_start->copy()->startOfWeek());
        $diffEndDayWeek = $date_end->copy()->endOfWeek()->diffInDays($date_end->copy()->endOfDay());
        $nbreWeeks = $date_end->copy()->endOfWeek()->diffInWeeks($date_start->copy()->startOfWeek());

        $start = $date_start->copy()->startOfWeek();
        $end = $date_start->copy()->endOfWeek();

        $rangerWeeks = [];

        $dj = 0;
        $j = 7;
        $m = 1;

        for($n = 0; $n <= $nbreWeeks; $n++){


            $rangerWeeks[$n]['text'] = $date_start->copy()->startOfWeek()->addDays($dj)->format('Y-m-d').' - '.$date_start->copy()->addDays(($dj))->endOfWeek()->format('Y-m-d');

            if(($n === 0)){

                $rangerWeeks[$n]['period_start'] = $start->copy()->addDays($diffFirstDayWeek);

            }else{

                $rangerWeeks[$n]['period_start'] = $date_start->copy()->startOfWeek()->addDays($dj);
            }

            if($n === $nbreWeeks){

                $rangerWeeks[$n]['period_end'] = $date_end->copy()->endOfWeek()->subDays($diffEndDayWeek);

            }else{

                $rangerWeeks[$n]['period_end'] = $end->copy()->addDays($dj);
            }

            $dj = ($j * $m);
            $m++;
        }

        return $rangerWeeks;
    }


    /**
     * @param $date_start
     * @param $date_end
     * @return array
     */
    protected function rangerPerMonths($date_start, $date_end){

        $diffFirstDayMonth = $date_start->copy()->startOfDay()->diffInDays($date_start->copy()->startOfMonth());
        $diffEndDayMonth = $date_end->copy()->endOfMonth()->diffInDays($date_end->copy()->endOfDay());
        $nbreMonth = $date_end->copy()->endOfMonth()->diffInMonths($date_start->copy()->startOfMonth());

        $start = $date_start->copy()->startOfMonth();
        $end = $date_start->copy()->endOfMonth();

        $rangerMonths = [];

        $dj = 0;

        for($n = 0; $n <= $nbreMonth; $n++){

            $rangerMonths[$n]['text'] = $date_start->copy()->startOfMonth()->addMonths($n)->format('Y-m');

            if(($n === 0)){

                $rangerMonths[$n]['period_start'] = $start->copy()->addDays($diffFirstDayMonth);

            }else{

                $rangerMonths[$n]['period_start'] = $date_start->copy()->startOfMonth()->addMonths($dj);
            }

            if($n === $nbreMonth){

                $rangerMonths[$n]['period_end'] = $date_end->copy()->endOfMonth()->subDays($diffEndDayMonth);

            }else{

                $rangerMonths[$n]['period_end'] = $end->copy()->addMonths($dj);
            }

        }

        return $rangerMonths;
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


    /**
     * @param $data
     * @param $lang
     * @return array
     */
    protected function statistiqueChannelExport($data, $lang){

        $data = $data['statistiqueChannel'];

        foreach ($data as $key => $value){

            $libelle[$key] = $value['name'][$lang];
            $total_claim[$key] = $value['total_claim'];
            $total_pourcentage[$key] = $value['pourcentage'];

        }

        return [
            'name' => $libelle,
            'total_claim' => $total_claim,
            'total_pourcentage' => $total_pourcentage
        ];
    }

    /**
     * @param $image
     * @return string
     */
    protected function getFileImage($image){

        $fileName = $image->file->extension();

        $image->file->move(public_path('assets/reporting/images'), $fileName);

        return asset('assets/reporting/images/'.$fileName);

    }

    protected function chanelGraphExport($data){

        $legend = $data['chanelGraph']['legend'];
        $image = $data['chanelGraph']['image'];

        foreach ($legend as $key => $value){

            $libelle[] = $key;
            $color[] = $value;

        }

        return [
            'libelle' => $libelle,
            'color' => $color,
            'image' => $image
        ];
    }

    /**
     * @param $data
     * @return array
     */
    protected function evolutionClaimExport($data){
        $legend = $data['evolutionClaim']['legend'];
        $image = $data['evolutionClaim']['image'];

        return [
            'legend' => $legend,
            'image' => $image
        ];
    }

    protected function dataPdf($data, $lang, $institution, $myInstitution = false){

        if($myInstitution){

            if($institution->id !== $data['filter']['institution']){

                throw new CustomException("Vous n'êtes pas autorité à accéder au reporting de cette insitution.");
            }
        }

        if(is_null($institution->logo)){

            $logo = asset('assets/reporting/images/regerg65_1588865981.png');
        }else{

            $logo = $institution->logo;
        }

        return  [
         'statistiqueObject' => $data['statistiqueObject'],
         'statistiqueQualificationPeriod' => $data['statistiqueQualificationPeriod'],
         'statistiqueTreatmentPeriod' => $data['statistiqueTreatmentPeriod'],
         'statistiqueChannel' => $this->statistiqueChannelExport($data, $lang),
         'chanelGraph' => $this->chanelGraphExport($data),
         'evolutionClaim' => $this->evolutionClaimExport($data),
         'periode' => $data['filter'],
         'logo' => $logo,
         'color_table_header' => '#7F9CF5',
         'lang' => $lang
        ];
    }




}