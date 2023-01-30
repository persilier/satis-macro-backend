<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Jobs\PdfReportingSendMail;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ReportingTask;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Metadata;


/**
 * Trait ReportingClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait ReportingClaim
{
    /**
     * @param bool $institution
     * @return array
     */
    protected function rules($institution = true)
    {

        $data = [

            'date_start' => 'date_format:Y-m-d',
            'date_end' => 'date_format:Y-m-d|after_or_equal:date_start'
        ];

        if($institution){

            $data['institution_id'] = 'sometimes|exists:institutions,id';
        }

        return $data;
    }

    /**
     * @param $request
     * @param $institution
     * @return Builder
     */
    protected function getAllClaims($request, $institution){

        $claims = Claim::with('activeTreatment');

        if($institution){

            $claims->whereHas('activeTreatment', function ($o) use ($request){

                $o->whereHas('responsibleUnit', function ($r) use ($request){

                    $r->where('institution_id', $request->institution_id);
                });

            })->has('treatments');
        }

        $claims->where('created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
            ->where('created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        return $claims;
    }


    /**
     * @param $request
     * @param $institution
     * @param string $condition
     * @return Builder
     */
    protected function qualificationTreatmentQuery($request, $institution, $condition = 'transferred_to_unit_at'){

        $claims = Claim::with('activeTreatment')->whereHas('activeTreatment', function ($o) use ($request, $institution, $condition){

            $o->where($condition, '!=', null);

            if($institution){

                $o->whereHas('responsibleUnit' , function ($p) use ($request, $institution){

                    $p->where('institution_id', $institution);

                });
            }

        })->has('treatments');

        $claims->where('created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
            ->where('created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        return $claims;

    }


    /*     Categories/Objects/Claims       */

    /**
     * @param $request
     * @param $institution
     * @return Builder[]|Collection
     */
    protected function getAllCategoryObjectsClaim($request, $institution)
    {
        return ClaimCategory::with(['claimObjects.claims' => function ($m) use ($request, $institution){

            $m->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay());

            if($institution){

                $m->whereHas('activeTreatment', function ($o) use ($request){

                    $o->whereHas('responsibleUnit', function ($r) use ($request){

                        $r->where('institution_id', $request->institution_id);
                    });

                })->has('treatments');
            }

        }])->whereHas('claimObjects.claims', function ($p) use ($request, $institution){

            if($institution){

                $p->whereHas('activeTreatment', function ($o) use ($request){

                    $o->whereHas('responsibleUnit', function ($r) use ($request){

                        $r->where('institution_id', $request->institution_id);
                    });

                })->has('treatments');
            }

            $p->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                    ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay());

        })->get();

        //return $categories

    }


    /**
     * @param $request
     * @param bool $institution
     * @return Builder[]|Collection|\Illuminate\Support\Collection|ReportingClaim[]
     */
    protected function statistiqueObjectsClaims($request, $institution = false){

        $categoriesObjectsclaims = $this->getAllCategoryObjectsClaim($request, $institution)->map(function ($item){

            $item->claimObjects->map(function ($object){

                $stats = $this->statistiqueClaims($object->claims);
                $object['total'] = $stats['total'];
                $object['incomplete'] = $stats['incomplete'];
                $object['toAssignementToUnit'] = $stats['toAssignementToUnit'];
                $object['toAssignementToStaff'] = $stats['toAssignementToStaff'];
                $object['awaitingTreatment'] = $stats['awaitingTreatment'];
                $object['toValidate'] = $stats['toValidate'];
                $object['toMeasureSatisfaction'] = $stats['toMeasureSatisfaction'];
                $object['percentage'] = $stats['percentage'];

                return $object;
            });

            return $item;
        });

        return $categoriesObjectsclaims;
    }


    /*           Channels                  */
    /**
     * @param $claims
     * @return mixed
     */
    protected function statistiqueClaims($claims){

        $total = 0;
        $incomplete = 0;
        $toAssignementToUnit = 0;
        $toMeasureSatisfaction = 0;
        $toAssignementToStaff = 0;
        $awaitingTreatment = 0;
        $toValidate = 0;
        $totalArchived = 0;
        $claimsResolue = 0;
        $percentage = 0 ;

        foreach ($claims as $claim){

            $total++;

            if($claim->status === 'incomplete'){

                $incomplete++;
            }

            if($claim->status === 'transferred_to_targeted_institution' || $claim->status === 'full'){

                $toAssignementToUnit++;
            }

            if(!is_null($claim->activeTreatment)){

                if($claim->status === 'transferred_to_unit'){

                    $toAssignementToStaff++;
                }

                if($claim->status === 'assigned_to_staff'){

                    $awaitingTreatment++;
                }

                if($claim->status === 'treated'){

                    $toValidate++;
                }

                if($claim->status === 'validated'){

                    $toMeasureSatisfaction++;
                }

                if($claim->status === 'archived'){

                    $totalArchived++;
                }


                if($claim->status === 'archived' && $claim->activeTreatment->is_claimer_satisfied === 1){

                    $claimsResolue++;
                }


            }

            $percentage = (($claimsResolue !== 0) && ($totalArchived!==0)) ? round((($claimsResolue/$totalArchived) * 100),2) : 0;

        }

        return [
            'total' => $total,
            'incomplete' => $incomplete,
            'toAssignementToUnit' => $toAssignementToUnit,
            'toAssignementToStaff' => $toAssignementToStaff,
            'awaitingTreatment' => $awaitingTreatment,
            'toValidate' => $toValidate,
            'toMeasureSatisfaction' => $toMeasureSatisfaction,
            'percentage' => $percentage
        ];

    }


    protected function statistiqueChannels($request, $institution =  false){

        $claimsQuery = $this->getAllClaims($request, $institution)->where('request_channel_slug', '!=', NULL)->get();

        $total = $claimsQuery->count();

        $claims = $claimsQuery->pluck('id')->toArray();

        $channels = Channel::with(['claims' => function ($q) use ($request, $institution){

            $q->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay());

            if($institution){

                $q->whereHas('activeTreatment', function ($o) use ($request){

                    $o->whereHas('responsibleUnit', function ($r) use ($request){

                        $r->where('institution_id', $request->institution_id);
                    });

                })->has('treatments');
            }

        }])->whereHas('claims', function ($c) use ($request, $claims){

            $c->whereIn('id', $claims);

        })->get()->map(function ($item) use ($total){

            $nbre = $item->claims->count();
            $item['total_claim'] = $nbre;
            $item['pourcentage'] = (($nbre !== 0) && ($total!==0)) ? round((($nbre/$total) * 100),2) : 0;

            return $item;
        });

        return $channels;
    }

    /*     Qualifications       */
    protected function statistiqueQualifications($request, $institution){

        $datas = $this->qualificationTreatmentQuery($request, $institution)->get();

        $totalCalaim = $datas->count();

        $data = [];

        $parameters = collect(json_decode(Metadata::where('name', 'delai-qualification-parameters')->firstOrFail()->data))->sortBy('borne_inf')->values()->all();

        $total = count($parameters);

        if($total > 0){

            foreach ($parameters as $key => $parameter){

                if($parameter->borne_sup === '+'){

                    $data[$key] = $this->countFilterQualificationPeriod($datas, $parameter->borne_inf, $parameter->borne_inf, $finish = true, $totalCalaim, 'Plus de '.$parameter->borne_inf." Jour(s)");

                }else{

                    $data[$key] = $this->countFilterQualificationPeriod($datas, $parameter->borne_inf, $parameter->borne_sup, $finish = false, $totalCalaim, $parameter->borne_inf."-".$parameter->borne_sup. " Jour(s)");

                }

            }

        }

        return $data;
    }


    /**
     * @param $datas
     * @param $valStart
     * @param $valEnd
     * @param bool $finish
     * @param $total
     * @param $libelle
     * @return mixed
     */
    protected  function countFilterQualificationPeriod($datas, $valStart, $valEnd, $finish, $total, $libelle){

        $data['libelle'] = $libelle;

        $data['total'] = $datas->filter(function ($item) use ($valStart, $valEnd, $finish){

            $diff = $item->completed_at->diffInDays($item->activeTreatment->transferred_to_unit_at, false);

            return ($valStart <= $diff && $valEnd > $diff);

        })->count();

        $data['pourcentage'] = (($data['total'] !== 0) && ($total !==0)) ? round((( $data['total']/$total) * 100),2) : 0;

        return $data;
    }


    /*     Traitements       */

    /**
     * @param $request
     * @param $institution
     * @return array
     */
    protected function statistiqueTreatments($request, $institution){

        $datas = $this->qualificationTreatmentQuery($request, $institution, 'satisfaction_measured_at')->get();

        $data = [];

        $totalCalaim = $datas->count();

        $parameters = collect(json_decode(Metadata::where('name', 'delai-treatment-parameters')->firstOrFail()->data))->sortBy('borne_inf')->values()->all();

        $total = count($parameters);

        if($total > 0){

            foreach ($parameters as $key => $parameter){

                if($parameter->borne_sup === '+'){

                    $data[$key] = $this->countFilterTreatmentPeriod($datas, $parameter->borne_inf, $parameter->borne_inf, $finish = true, $totalCalaim, 'Plus de '.$parameter->borne_inf." Jour(s)");

                }else{

                    $data[$key] = $this->countFilterTreatmentPeriod($datas, $parameter->borne_inf, $parameter->borne_sup, $finish = false, $totalCalaim, $parameter->borne_inf."-".$parameter->borne_sup. " Jour(s)");

                }

            }

        }

        return $data;
    }


    /**
     * @param $datas
     * @param $valStart
     * @param $valEnd
     * @param bool $finish
     * @param $total
     * @param $libelle
     * @return mixed
     */
    protected  function countFilterTreatmentPeriod($datas, $valStart, $valEnd, $finish, $total, $libelle){

        $data['libelle'] = $libelle;

        $data['total'] = $datas->filter(function ($item) use ($valStart, $valEnd, $finish){

            $diff = $item->activeTreatment->transferred_to_unit_at->diffInDays($item->activeTreatment->satisfaction_measured_at, false);

            return ($valStart <= $diff && $valEnd > $diff);

        })->count();

        $data['pourcentage'] = ( ($data['total'] !== 0) && ($total !==0)) ? round((( $data['total']/$total) * 100),2) : 0;

        return $data;
    }


    /*    ReportingTasks      */

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
            ],
        ];
    }

    /**
     * @return array
     */
    protected function typeList(){

        return Constants::reportTypes();
    }


    /**
     * @return Builder[]|Collection
     */
    protected function getAllStaffsReportingTasks(){

        $institution = $this->institution();

        return Staff::query()->with('identite')->whereHas('identite', function ($query){
            $query->whereNotNull('email');
        })->where('institution_id', $institution->id)->get();
    }


    /**
     * @param bool $institution
     * @return array
     */
    protected function rulesTasksConfig($institution = true)
    {
        $data = [
            'period' => ['required', Rule::in(['days', 'weeks', 'months', 'quarterly', 'biannual'])],
            'reporting_type' => ['required', Rule::in(Constants::getReportTypesNames())],
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
     * @return void
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
            "reporting_type"=>$request->reporting_type
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


        if(ReportingTask::query()
            ->where('period', $request->period)
            ->where('reporting_type', $request->reporting_type)
            ->where('institution_targeted_id',$request->institution_id)
            ->where('institution_id', $institution->id)->where('id', '!=', $reportingTask)
            ->first()){
            throw new CustomException(__('messages.config_already_exist',[],app()->getLocale()));
        }
    }


    /*            Export Data PDF               */

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

                throw new CustomException(__('messages.not_allowed_to_access_reporting',[],app()->getLocale()));
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
            'logoSatis' => asset('assets/reporting/images/satisLogo.png'),
            'color_table_header' => "#7F9CF5",
            'lang' => $lang
        ];
    }


    /**
     * @param $request
     * @param $reportinTask
     * @return array
     */
    protected function dataPdfAuto($request, $reportinTask){

        $logo = asset('assets/reporting/images/satisLogo.png');

        $recepient = $reportinTask->institution;

        $institution = false;

        if(!is_null($reportinTask->institutionTargeted)){

            $request->merge(['institution_id' => $reportinTask->institutionTargeted->id]);

           if(!is_null($reportinTask->institutionTargeted->logo)){

               $logo = $reportinTask->institutionTargeted->logo;

           };

            $institution = true;

        }else{

            if(!is_null($recepient->logo)){

                $logo = $recepient->logo;

            }

        }

        return [

            'statistiqueObject' => $this->statistiqueObjectsClaims($request, $institution),
            'statistiqueQualificationPeriod' => $this->statistiqueQualifications($request, $institution),
            'statistiqueTreatmentPeriod' => $this->statistiqueTreatments($request, $institution),
            //'statistiqueChannel' => $this->statistiqueChannels($request, $institution),
            //'statistiqueGraphePeriod' => $this->statistiqueEvolutions($request, $institution),
            'logo' => $logo,
            'logoSatis' => asset('assets/reporting/images/satisLogo.png'),
            'periode' => $this->periodeFormat(['startDate' => $request->date_start->format('Y-m-d'), 'endDate' => $request->date_end->format('Y-m-d')]),
            'color_table_header' => '#7F9CF5',
        ];
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
     * @param $data
     * @return array
     */
    protected function chanelGraphExport($data){

       return $data['chanelGraph']['image'];

    }


    /**
     * @param $data
     * @return array
     */
    protected function evolutionClaimExport($data){

        return $data['evolutionClaim']['image'];

    }


    /**
     * @param $data
     * @return mixed
     */
    protected function periodeFormat($data){

        $data['startDate'] = !empty($data['startDate']) ? Carbon::parse($data['startDate'])->startOfDay() :  now()->startOfMonth()->subMonths(11);
        $data['endDate'] = !empty($data['endDate']) ? Carbon::parse($data['endDate'])->endOfDay()  : now()->endOfMonth();
        $data['libellePeriode'] = $this->libellePeriode($data);

        return $data;

    }



    /**
     * @param $request
     * @param $institutionId
     * @return Collection|Claim[]
     */
    /*protected function queryNumberObject($request, $institutionId){

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

    }*/




    /**
     * @param $request
     * @param $institutionId
     * @return int
     */
    /*protected function claimResolueStatistique($request, $institutionId){

        return $claims = $this->numberClaimByPeriod($request, $institutionId, 'satisfaction_measured_at', 'satisfaction_measured_at')->filter( function ($item){

            return ($item->is_claimer_satisfied === 1);

        })->count();


    }*/


    /**
     * @param $request
     * @param bool $institution
     * @return mixed
     */
    /* protected function statistique($claims, $objectClaimId, $status, $total = false){

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
                        return (($item->claim_object_id === $objectClaimId) && (/*($item->status === 'unfounded') || ($item->status === 'validated') ||*/ /*($item->status === $status)));*/
                    /*})->count();

                default:

                    return $claims->filter(function ($item) use ($objectClaimId , $status){
                        return (($item->claim_object_id === $objectClaimId) && ($item->status === $status));
                    })->count();
            }

        }

    }*/








    /**
     * @param $request
     * @param bool $institutionId
     * @return int
     */
    /*protected function countClaimWithoutChannel($request, $institutionId = false){

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
    }*/




    /*protected function treatmentPeriod($request, $institution){

        $claims = Claim::with('activeTreatment')->whereHas('activeTreatment', function ($o) use ($request, $institution){

            $o->where('satisfaction_measured_at', '!=', null);

            if($institution){

                $o->whereHas('responsibleUnit' , function ($p) use ($request, $institution){

                    $p->where('institution_id', $institution);

                });
            }

        })->has('treatments');

        $claims->where('created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
            ->where('created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        return $claims;

    }*/






    /**
     * @param $request
     * @param bool $institutionId
     * @return array
     */
    /*protected function treatmentPeriod($request, $institutionId = false){

        $totalClaim = $this->numberClaimByTreatmentPeriod($request, $institutionId)->count();

        $datas = $this->numberClaimByTreatmentPeriod($request, $institutionId);

        return [
            '0-2' => $this->countFilterBetweenDateTreatmentPeriod($datas, 0, 2, $finish = false, $totalClaim),
            '2-4' => $this->countFilterBetweenDateTreatmentPeriod($datas, 2, 4, $finish = false, $totalClaim),
            '4-6' => $this->countFilterBetweenDateTreatmentPeriod($datas, 4, 6, $finish = false, $totalClaim),
            '6-10' => $this->countFilterBetweenDateTreatmentPeriod($datas, 6, 10, $finish = false, $totalClaim),
            '+10' => $this->countFilterBetweenDateTreatmentPeriod($datas, 10, 10, $finish = true, $totalClaim),
        ];

    }*/







    /**
     * @param $request
     * @param $institutionId
     * @param string $condition
     * @param string $orderBy
     * @return Builder[]|Collection
     */
    /*protected  function numberClaimByPeriod($request, $institutionId, $condition = 'transferred_to_unit_at', $orderBy = 'completed_at'){

        $claims = $this->queryClaimByPeriod($institutionId, $orderBy);

        if($request->has('date_start') && $request->has('date_end')){
            $claims->where('claims.created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('claims.created_at', '<=',Carbon::parse($request->date_end)->endOfDay());
        }

        return $claims->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id');
        })->where('treatments.'.$condition, '!=', null)->select('claims.*')->get();

    }*/


    /**
     * @param $institutionId
     * @param $orderBy
     * @return Builder
     */
    /*protected function queryClaimByPeriod($institutionId, $orderBy){
        if(!$institutionId){
            $claims = Claim::with('activeTreatment')->orderBy($orderBy, 'ASC');
        }else{
            $claims =  Claim::with('activeTreatment')->orderBy($orderBy, 'ASC')->where('institution_targeted_id', $institutionId);
        }

        return $claims;
    }*/


    /**
     * @param $request
     * @param $institutionId
     * @param string $condition
     * @param string $orderBy
     * @return Builder[]|Collection
     */
    /*protected  function numberClaimByTreatmentPeriod($request, $institutionId, $condition = 'satisfaction_measured_at', $orderBy = 'satisfaction_measured_at'){

        $claims = $this->queryClaimByTreatmentPeriod($institutionId);

        if($request->has('date_start') && $request->has('date_end')){
            $claims->where('claims.created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('claims.created_at', '<=',Carbon::parse($request->date_end)->endOfDay());
        }

        return $claims->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id');
        })->orderBy('treatments.'.$orderBy)->where('treatments.'.$condition, '!=', null)->select('claims.*')->get();

    }*/

    /**
     * @param $institutionId
     * @return Builder
     */
    /*protected function queryClaimByTreatmentPeriod($institutionId){

        if(!$institutionId){
            $claims = Claim::with('activeTreatment');
        }else{
            $claims =  Claim::with('activeTreatment')->where('institution_targeted_id', $institutionId);
        }

        return $claims;
    }*/


    /**
     * @param $request
     * @param $institutionId
     * @return Claim
     */
    /*protected function queryClaimByDayOrMonthOrYear($request, $institutionId){

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
    }*/


    /**
     * @param $request
     * @param $institutionId
     * @return Builder
     */
    /*protected function queryClaimByDayOrMonthOrYearResolue($request, $institutionId){

        $claims = $this->queryClaimByTreatmentPeriod($institutionId);

        if($request->has('date_start') && $request->has('date_end')){

            $claims->where('claims.created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('claims.created_at', '<=',Carbon::parse($request->date_end)->endOfDay());
        }

        return $claims->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id');
        })->where('claims.status',  'validated')->orWhere('claims.status','unfounded')->orWhere('claims.status','archived')->select('claims.*');

    }*/


    /**
     * @param $request
     * @param $institution
     * @return mixed
     */
    protected  function statistiqueEvolutions($request, $institution){

        $claims_requests = $this->getAllClaims($request, $institution)->get();

        $claims_resolues = $this->qualificationTreatmentQuery($request, $institution, 'satisfaction_measured_at')->where('status', 'archived')->get();

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
     * @param $image
     * @return string
     */
    protected function getFileImage($image){

        $fileName = $image->file->extension();

        $image->file->move(public_path('assets/reporting/images'), $fileName);

        return asset('assets/reporting/images/'.$fileName);

    }


    /**
     * @param $value
     * @param $dateCron
     * @return Builder[]|Collection
     */
    protected function getAllReportingTasks($value, $dateCron){

        return ReportingTask::with(['institution', 'institutionTargeted'])->whereDoesntHave(
            'cronTasks',  function($query) use ($dateCron){
            $query->where('created_at', '>=', Carbon::parse($dateCron->copy())->startOfDay())
                ->where('created_at', '<=',Carbon::parse($dateCron->copy())->endOfDay());
        })->where('period', $value)->get();

    }

    /**
     * @param $request
     * @param $institution
     * @param $institutionId
     * @return array
     */
    protected function generateReportingAuto($request, $institution){

        $statistiques = [

            'statistiqueObject' => $this->statistiqueObjectsClaims($request, $institution),
            'statistiqueChannel' => $this->statistiqueChannels($request, $institution),
            'statistiqueQualificationPeriod' => $this->statistiqueQualifications($request, $institution),
            'statistiqueTreatmentPeriod' => $this->statistiqueTreatments($request, $institution),
            'statistiqueGraphePeriod' => $this->statistiqueEvolutions($request, $institution),

        ];

        $datas = [
            'filter' => [
                'institution' => $request->institution_id,
                'startDate' => $request->date_start->format('Y-m-d'),
                'endtDate' => $request->date_end->format('Y-m-d'),
            ],
            'statistiqueObject' => [
                'data' => $statistiques['statistiqueObject']
            ],
            'statistiqueQualificationPeriod' => $statistiques['statistiqueQualificationPeriod'],
            'statistiqueTreatmentPeriod' => $statistiques['statistiqueTreatmentPeriod'],
            /*'statistiqueChannel' => $statistiques['statistiqueChannel'],
            'chanelGraph' => [
                'image' => ''
            ],
            'evolutionClaim' => [
                'image' => ''
            ],*/
            "headeBackground" => "#7F9CF5"
        ];

        return $datas;

        /*if($request->has('institution_id')){
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

        return $statistiques;*/

    }

    /**
     * @param $request
     * @param $institutionId
     * @return Collection|\Illuminate\Support\Collection|ClaimCategory[]
     */
    /*protected  function addDataTotalInStatistiqueObject($request, $institutionId){

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
    }*/


    /**
     * @param $request
     * @param $reportinTask
     * @throws \Throwable
     */
    protected function TreatmentReportingTasks($request, $reportinTask){

        $data = view('ServicePackage::reporting.pdf-auto', $this->dataPdfAuto($request, $reportinTask))->render();

        $file = public_path().'/temp/Reporting_'.time().'.pdf';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);
        $pdf->save($file);

        $details = [
            'title' => $this->getMetadataByName(Constants::BIANNUAL_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::BIANNUAL_REPORTING)->description,
            'file' => $file,
            'email' => $this->emailDestinatairesReportingTasks($reportinTask),
            'reportingTask' => $reportinTask,
            'period' =>  $this->periodeFormat(['startDate' => $request->date_start->format('Y-m-d'), 'endDate' => $request->date_end->format('Y-m-d')])['libellePeriode'],
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




    protected function getAllDelaiParameters($typeParameters){

        return $parameters = collect(json_decode(Metadata::where('name', $typeParameters)->first()->data))->sortBy('borne_inf')->values()->all();
    }


    /**
     * @param $typeParameters
     * @param $parameter
     * @return mixed
     */
    protected  function getOneDelaiParameters($typeParameters, $parameter){

        $parameters = collect(json_decode(Metadata::where('name', $typeParameters)->first()->data))->sortBy('borne_inf')->values()->all();

        $data = Arr::first($parameters, function ($item) use ($parameter){
            return ($item->uuid === $parameter);
        });

        if(is_null($data)){

            throw new CustomException(__('messages.cant_get_config',[],getAppLang()));
        }

        return $data;
    }


    /**
     * @param $typeParameters
     * @param $parameter
     * @return mixed
     */
    protected  function destroyDelaiParameters($typeParameters, $parameter){

        $parameters = $this->getAllDelaiParameters($typeParameters);

        $parameter = $this->getOneDelaiParameters($typeParameters, $parameter);

        $data = Arr::where($parameters, function ($item) use ($parameter){
            return ($item->uuid !== $parameter->uuid);
        });

        Metadata::where('name', $typeParameters)->firstOrFail()->update(['data' => json_encode($data)]);

        return $parameter;
    }


    /**
     * @return array
     */
    protected function rulesParameters(){

        return  [
            'borne_inf' => 'required|integer',
            'borne_sup' => 'required'
        ];
    }

    /**
     * @param $request
     * @param $parameters
     * @param $infinite
     */
    protected function verifiedStore($request, $parameters, $infinite){

        if(!$infinite){

            if($request->borne_sup <= $request->borne_inf)
                throw new CustomException(__('messages.bound_alert',[],getAppLang()));

        }

        if(!is_null($parameters)){

            foreach($parameters as $parameter){


                if($parameter->borne_sup !== '+'){

                    if(!($request->borne_inf >= $parameter->borne_sup && $request->borne_inf > $parameter->borne_inf) && !($request->borne_sup <= $parameter->borne_inf && $request->borne_inf < $parameter->borne_inf)){

                        throw new CustomException(__('messages.invalid_config',[],getAppLang()));

                    }

                }else{

                    if($infinite){

                        throw new CustomException(__('messages.invalid_config',[],getAppLang()));

                    }else{

                        if(!($request->borne_sup <= $parameter->borne_inf && $request->borne_inf < $parameter->borne_inf)){

                            throw new CustomException(__('messages.invalid_config',[],getAppLang()));

                        }
                    }

                }
            }
        }

    }


    /**
     * @param $request
     * @param $parameters
     * @param $typeParameters
     * @return array
     */
    protected function storeParameters($request, $parameters, $typeParameters){

        $data = [

            'uuid' => (string) Str::uuid(),
            'borne_inf' => $request->borne_inf,
            'borne_sup' => $request->borne_sup,
        ];

        array_push($parameters, (object) $data);
        Metadata::where('name', $typeParameters)->firstOrFail()->update(['data' => json_encode($parameters)]);

        return $data;
    }


}
