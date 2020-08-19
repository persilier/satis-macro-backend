<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Jobs\PdfReportingSendMail;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\ReportingTask;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailValidationRules;


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

        $objects = ClaimObject::has('claims')->where('claim_category_id', $claimCategoryId)->get()->map(function ($item) use ($claims){
            $claimsResolue = $this->statistique($claims, $item->id, 'validated');
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
                case 'resolue':

                    return $claims->filter(function ($item) use ($objectClaimId , $status){
                        return (($item->claim_object_id === $objectClaimId) && ($item->satisfaction_measured_by !== null));
                    })->count();

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

            if(($item->created_at >= $value['period_start']) && ($item->created_at <= $value['period_end']))
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

            $rangerMonths[$n]['text'] = $date_start->copy()->startOfMonth()->addMonthsNoOverflow($n)->format('Y-m');

            if(($n === 0)){

                $rangerMonths[$n]['period_start'] = $start->copy()->addDays($diffFirstDayMonth);

            }else{

                $rangerMonths[$n]['period_start'] = $date_start->copy()->startOfMonth()->addMonthsNoOverflow($dj);
            }

            if($n === $nbreMonth){

                $rangerMonths[$n]['period_end'] = $date_end->copy()->endOfMonth()->subDays($diffEndDayMonth);

            }else{

                $rangerMonths[$n]['period_end'] = $end->copy()->addMonthsNoOverflow($dj);
            }

            $dj = $dj +1;

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
        $type = $data['evolutionClaim']['filter'];

        return [
            'legend' => $legend,
            'image' => $image,
            'type_graphe' => $type
        ];
    }

    /**
     * @param $data
     * @param $lang
     * @param $institution
     * @param bool $myInstitution
     * @return array
     */
    protected function dataPdf($data, $lang, $institution, $myInstitution = false){


        if($myInstitution){

            if($institution->id !== $data['filter']['institution']){

                throw new CustomException("Vous n'êtes pas autorité à accéder au reporting de cette insitution.");
            }
        }

        if(is_null($institution->logo)){

            $logo = asset('assets/reporting/images/satisLogo.png');

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
             'periode' => $this->periodeFormat($data['filter']),
             'logo' => $logo,
             'color_table_header' => $data['headeBackground'],
             'lang' => $lang
        ];
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function periodeFormat($data){

        $data['startDate'] = !empty($data['startDate']) ? Carbon::parse($data['startDate'])->startOfDay()->translatedFormat('l, jS F Y') : now()->startOfYear()->translatedFormat('l, jS F Y');
        $data['endDate'] = !empty($data['endDate']) ? Carbon::parse($data['endDate'])->endOfDay()->translatedFormat('l, jS F Y')  : now()->endOfYear()->translatedFormat('l, jS F Y');
        return $data;

    }


    /**
     * @param $value
     * @param $date
     * @return Builder[]|Collection
     */
    protected function getAllReportingTasks($value, $dateCron){

        $reportinTasks = ReportingTask::with(['institution', 'institutionTargeted'])->whereDoesntHave(
            'cronTasks',  function($query) use ($dateCron){
                $query->where('created_at', '>=', Carbon::parse($dateCron->copy())->startOfDay())
                    ->where('created_at', '<=',Carbon::parse($dateCron->copy())->endOfDay());
        })->where('period', $value)->get();

        return $reportinTasks;
    }

    /**
     * @param $request
     * @param $institution
     * @param $institutionId
     * @return array
     */
    protected function generateReportingAuto($request, $institution, $institutionId){


        if($request->has('institution_id')){
            $institutionId = $request->institution_id;
        }

        $lang = app()->getLocale();

        $filter = [
            'institution' => $request->institution_id,
            'startDate' => $request->date_start,
            'endDate' => $request->date_end,
        ];

        if(is_null($institution->logo)){

            $logo = asset('assets/reporting/images/satisLogo.png');

        }else{

            $logo = $institution->logo;
        }

        $statistiques =  [
            'statistiqueObject' => $this->addDataTotalInStatistiqueObject($request, $institutionId),
            'statistiqueQualificationPeriod' => $this->qualificationPeriod($request, $institutionId),
            'statistiqueTreatmentPeriod' =>  $this->treatmentPeriod($request, $institutionId),
            //'statistiqueChannel' =>  $this->numberChannels($request, $institutionId),
            'periode' =>  $this->periodeFormat($filter),
            'logo' => $logo,
            'color_table_header' => '#7F9CF5',
            'lang' => $lang
        ];

        return $statistiques;

    }

    /**
     * @param $request
     * @param $institutionId
     * @return Collection|\Illuminate\Support\Collection|ClaimCategory[]
     */
    protected  function addDataTotalInStatistiqueObject($request, $institutionId){

        $dataTotal = [
            'totalCollect' => 0,
            'totalIncomplete' => 0,
            'totalToAssignUnit' => 0,
            'totalToAssignStaff' => 0,
            'totalAwaitingTreatment' => 0,
            'totalToValidate' => 0,
            'totalToMeasureSatisfaction' => 0,
            'totalPercentage' => 0,
        ];

        $statistiqueObject = $this->numberClaimByObject($request, $institutionId);

        foreach ($statistiqueObject as $category) {

            if($category->claim_objects->isNotEmpty()){

                foreach ($category->claim_objects as $value){

                    $dataTotal['totalCollect'] = $dataTotal['totalCollect'] + $value->total;
                    $dataTotal['totalIncomplete'] = $dataTotal['totalIncomplete'] + $value->incomplete;
                    $dataTotal['totalToAssignUnit'] = $dataTotal['totalToAssignUnit'] + $value->toAssignementToUnit;
                    $dataTotal['totalToAssignStaff'] = $dataTotal['totalToAssignStaff'] + $value->toAssignementToStaff;
                    $dataTotal['totalAwaitingTreatment'] = $dataTotal['totalAwaitingTreatment'] + $value->awaitingTreatment;
                    $dataTotal['totalToValidate'] = $dataTotal['totalToValidate'] + $value->toValidate;
                    $dataTotal['totalToMeasureSatisfaction'] = $dataTotal['totalToMeasureSatisfaction'] + $value->toMeasureSatisfaction;
                    $dataTotal['totalPercentage'] = ($dataTotal['totalPercentage'] + $value->percentage);

                }

            }

        };

        $statistique['data']  = $statistiqueObject;
        $statistique['total'] = $dataTotal;

        return $statistique;
    }


    /**
     * @param $request
     * @param $reportinTask
     * @throws \Throwable
     */
    protected function TreatmentReportingTasks($request, $reportinTask){

        $institutionId = false;

        if(!is_null($reportinTask->institutionTargeted)){

            $request->merge(['institution_id' => $reportinTask->institutionTargeted->id]);

        }

        $institution = $reportinTask->institution;

        $rapportData = $this->generateReportingAuto($request, $institution, $institutionId);

        $data = view('ServicePackage::reporting.pdf-auto', $rapportData)->render();

        $file = public_path().'/temp/Reporting_'.time().'.pdf';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);
        $pdf->save($file);

        $dd = $request->date_start;
        $df = $request->date_end;

        $details = [
            'file' => $file,
            'email' => $this->emailDestinatairesReportingTasks($reportinTask),
            'reportingTask' => $reportinTask,
            'dateStart' => $dd->format('Y-m-d'),
            'dateEnd' => $df->format('Y-m-d'),
        ];

        PdfReportingSendMail::dispatch($details);
    }


    /**
     * @param $reportingTask
     * @return array
     */
    protected function emailDestinatairesReportingTasks($reportingTask){

        $emails = [];

        $staffs = Staff::with('reportingTasks', 'identite')->whereHas('identite', function ($q){

            $q->whereNotNull('email');

        })->whereHas('reportingTasks', function($query) use ($reportingTask){

            $query->where('id', $reportingTask->id);

        })->get();

        foreach($staffs as $staff){

            $emails[] = $staff->identite->email[0];
        }

        return $emails;

    }


    /* CONFIGURATIONS*/
    /**
     * @param bool $institution
     * @return array
     */
    protected function rulesTasksConfig($institution = true)
    {
        $data = [
            'period' => ['required', Rule::in(['days', 'weeks', 'months', 'quarterly', 'biannual'])],
            'staffs' => [
                'required', 'array',
            ],
        ];

        if($institution){

            $data['institution_id'] = 'nullable|exists:institutions,id';

        }

        return $data;
    }

    /**
     * @param $request
     * @return array
     */
    protected function verifiedStaffsExist($request){

        foreach ($request->staffs as $staff){

            Staff::findOrFail($staff);

        }

    }

    /**
     * @param $request
     * @param $institution
     * @return array
     */
    protected  function createFillableTasks($request, $institution){

        $data = [

            'institution_id' => $institution->id,
            'period' => $request->period,
        ];

        if($request->has('institution_id')){

            $data['institution_targeted_id'] = $request->institution_id;

        }

        return $data;
    }

    /**
     * @param $request
     * @param $institution
     * @param null $reportingTask
     */
    protected function reportingTasksExists($request, $institution, $reportingTask = null){

        if(ReportingTask::where('period', $request->period)->where('institution_targeted_id',$request->institution_id)
            ->where('institution_id', $institution->id)->where('id', '!=', $reportingTask)->first()){
            throw new CustomException("Cette configuration de rapport automatique existe déjà pour la période choisie.");
        }
    }

    /**
     * @param $institution
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    protected function reportingTasksMap($institution){

        return ReportingTask::with('institutionTargeted', 'staffs.identite')->where('institution_id', $institution->id)->get()->map(function($item){

            $item['period_tag'] =  Arr::first($this->periodList(), function ($value) use ($item){
                return $value['value'] === $item->period;
            });

            return $item;

        });
    }


    /**
     * @return Builder[]|Collection
     */
    protected function getAllStaffsReportingTasks(){

        $institution = $this->institution();

        return Staff::with('identite')->whereHas('identite', function ($query){

            $query->whereNotNull('email');

        })->where('institution_id', $institution->id)->get();

    }


    /**
     * @param $reportingTask
     * @return mixed
     */
    protected function reportingTaskMap($reportingTask){

        $reportingTask->load('institutionTargeted', 'staffs.identite');

        $reportingTask['period_tag'] = Arr::first($this->periodList(), function ($value) use ($reportingTask){
            return $value['value'] === $reportingTask->period;
        });

        return $reportingTask;
    }

    /**
     * @return array
     */
    protected function periodList(){

        return [
            [
                'value' => 'days', 'label' => 'Journalier'
            ],
            [
                'value' => 'weeks', 'label' => 'Hebdomadaire'
            ],
            [
                'value' => 'months', 'label' => 'Mensuel'
            ],
            [
                'value' => 'quarterly', 'label' => 'Trimestriel'
            ],
            [
                'value' => 'biannual', 'label' => 'Semestriel'
            ]
        ];
    }

}