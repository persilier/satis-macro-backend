<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
trait FilterClaims
{

    /**
     * @param $request
     * @param array $relations
     * @return Builder
     */
    protected function getAllClaimsByPeriod($request,$relations=[]){

        $claims = Claim::query()->with($relations);

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        return $claims;
    }

    /**
     * @param $claims
     * @return Builder
     */
    protected function getClaimsReceivedBySeverityLevel($claims){

        return $claims
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'claim_objects.severity_levels_id')
            ->selectRaw('severity_levels.name,severity_levels.id, count(*) as total')
            ->groupBy('severity_levels.name','severity_levels.id');

    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsReceivedWithClaimObjectBySeverityLevel($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'claim_objects.severity_levels_id')
            ->whereNotNull('claim_object_id')
            ->selectRaw('severity_levels.name,severity_levels.id, count(*) as total')
            ->groupBy('severity_levels.name','severity_levels.id');

        return $claims;

    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsTreatedBySeverityLevel($request){

        $claims = Claim::query();
        $claims->where('claims.status', Claim::CLAIM_VALIDATED);
        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims = $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'claim_objects.severity_levels_id')
            ->where('treatments.validated_at', '>=', Carbon::parse($request->date_start)->startOfDay())
            ->where('treatments.validated_at', '<=', Carbon::parse($request->date_end)->endOfDay())
            ->selectRaw('severity_levels.name,severity_levels.id, count(*) as total')
            ->groupBy('severity_levels.name','severity_levels.id');

        return $claims;

    }

    /**
     * @param $request
     * @param null $unitId
     * @return Builder
     */
    protected function getClaimsReceivedByClaimObject($request, $unitId=null){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        if($unitId==null){

            $claims = $claims
                ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
                ->selectRaw('claim_objects.name, count(*) as total')
                ->groupBy('claim_objects.name')
                ->orderByDesc('total');

            return $claims;
        }else{

            $claims = $claims
                ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
                ->where('unit_targeted_id', $unitId)
                ->selectRaw('claim_objects.name, count(*) as total')
                ->groupBy('claim_objects.name')
                ->orderByDesc('total');

            return $claims;
        }


    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsReceivedByClaimCategory($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->leftJoin('claim_categories', 'claim_categories.id', '=', 'claim_objects.claim_category_id')
            ->selectRaw('claim_categories.name, count(*) as total')
            ->groupBy('claim_categories.name')
            ->orderByDesc('total');
           // ->orderBy('total','Asc');

        return $claims;

    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsReceivedByClientCategory($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('identites', 'identites.id', '=', 'claims.claimer_id')
            ->leftJoin('clients', 'clients.identites_id', '=', 'identites.id')
            ->leftJoin('client_institution', 'client_institution.client_id', '=', 'clients.id')
            ->leftJoin('category_clients', 'category_clients.id', '=', 'client_institution.category_client_id')
            ->selectRaw('category_clients.name, count(*) as total')
            ->groupBy('category_clients.name');

        return $claims;
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsReceivedByClientGender($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('identites', 'identites.id', '=', 'claims.claimer_id')
            ->selectRaw('identites.sexe, count(*) as total')
            ->groupBy('identites.sexe')
            ->orderByDesc('total');

        return $claims;
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsReceivedByUnit($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
            ->selectRaw('units.name,units.id, count(*) as total')
            ->groupBy('units.name','units.id')
            ->orderByDesc('total');

        return $claims;
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsTreatedByUnit($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims = $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->leftJoin('units', 'units.id', '=', 'treatments.responsible_unit_id')
            ->where('treatments.transferred_to_unit_at', '>=', Carbon::parse($request->date_start)->startOfDay())
            ->where('treatments.transferred_to_unit_at', '<=', Carbon::parse($request->date_end)->endOfDay())
            ->selectRaw('units.name, count(*) as total')
            ->groupBy('units.name')
            ->orderByDesc('total');

        return $claims;
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsByRequestChanel($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('channels', 'channels.slug', '=', 'claims.request_channel_slug')
            ->selectRaw('channels.slug, count(*) as total')
            ->groupBy('channels.slug')
            ->orderByDesc('total');
        return $claims;

    }

    /**
     * @param $request
     * @param $status
     * @param array $relations
     * @return Builder
     */
    function getClaimsTreatedByPeriod($request, $status, $relations=[])
    {
        $claims = Claim::query()->with($relations);

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->join('treatments', function ($join) {
                $join->on('claims.id', '=', 'treatments.claim_id');
         })->select('claims.*');

        $claims ->where('validated_at', '>=', Carbon::parse($request->date_start)->startOfDay())
                ->where('validated_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims->where('status', $status);

        return $claims;
    }

    /**
     * @param $request
     * @param $status
     * @param array $relations
     * @return Builder
     */
    protected function getClaimsSatisfactionMeasured($request, $status, $relations=[]){

        $claims = Claim::query()->with($relations);

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id');
        })->select('claims.*');

        $claims ->where('satisfaction_measured_at', '>=', Carbon::parse($request->date_start)->startOfDay())
                ->where('satisfaction_measured_at', '<=', Carbon::parse($request->date_end)->endOfDay())
                ->where('claims.status', $status);

        return $claims;

    }


    /**
     * @param $request
     * @return Builder[]|Collection|int
     */
    protected function getClaimsSatisfaction($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }
        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->whereNotNull('satisfaction_measured_at')
            ->where('is_claimer_satisfied','=',1)
            ->when($request->has('unit_targeted_id'),function ($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name,units.id, count(*) as total')
                    ->groupBy('units.name','units.id')
                    ->orderByDesc('total');
            });

        return $request->has('unit_targeted_id') ? $claims->get():$claims->count();

    }


    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsDissatisfied($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
            ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());


        $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->whereNotNull('satisfaction_measured_at')
            ->where('is_claimer_satisfied','=',0);

        return $claims;

    }


    /**
     * @param $request
     * @return Builder|Builder[]|Collection
     */
    protected function getClaimsSatisfactionAfterTreatment($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->whereNotNull('satisfaction_measured_at')
            ->when($request->has('unit_targeted_id'),function ($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name,units.id, count(*) as total')
                    ->groupBy('units.name','units.id')
                    ->orderByDesc('total');
            });

        return $request->has('unit_targeted_id') ? $claims->get():$claims;

    }


    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsResolved($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->whereNotNull('treatments.satisfaction_measured_at')
            ->when($request->has('unit_targeted_id'),function ($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name,units.id, count(*) as total')
                    ->groupBy('units.name','units.id')
                    ->orderByDesc('total');
            });

        return $request->has('unit_targeted_id') ? $claims->get():$claims->count();

    }


    /**
     * @param $request
     * @return Builder[]|Collection|int
     */
    protected function getClaimsUnresolved($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->whereNull('treatments.satisfaction_measured_at')
            ->when($request->has('unit_targeted_id'),function ($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name,units.id, count(*) as total')
                    ->groupBy('units.name','units.id')
                    ->orderByDesc('total');
            });

        return $request->has('unit_targeted_id') ? $claims->get():$claims->count();

    }

    /**
     * @param $claims
     * @param $status
     * @param array $relations
     * @param bool $treatment
     * @return Builder
     */
    function getClaimsByStatus($claims, $status, $relations=[], $treatment=false)
    {

        if ($treatment) {

            $claims->join('treatments', function ($join) {

                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })->select('claims.*');
        }

        if ($status === 'transferred_to_targeted_institution') {

            $claims->where('status', 'full')->orWhere('status', 'transferred_to_targeted_institution');

        } else {

            $claims->where('status', $status);
        }

        return $claims;
    }

    /**
     * @param $request
     * @param $institution
     * @return Builder[]|Collection
     */
    protected function getAllClaimsByCategoryObjects($request, $institution)
    {
        return ClaimCategory::with(['claimObjects.claims' => function ($m) use ($request, $institution){

            $m->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay());

            if ($request->has('institution_id')) {

                $m->where('institution_targeted_id', $request->institution_id);

            }

        }])->whereHas('claimObjects.claims', function ($p) use ($request, $institution){

            if ($request->has('institution_id')) {

                $p->where('institution_targeted_id', $request->institution_id);

            }

            $p->where('created_at', '>=',Carbon::parse($request->date_start)->startOfDay())
                    ->where('created_at', '<=',Carbon::parse($request->date_end)->endOfDay());

        })->get();


    }

    /**
     * @param $claims
     * @return mixed
     */
    public function getUnTreatedClaims($claims)
    {
        return $claims
            ->where('status','!=',Claim::CLAIM_TREATED);
    }


    /**
     * @param $claims
     * @return mixed
     */
    public function getRevivalClaims($claims)
    {
        return $claims
            ->where('is_revival',true);
    }

    /**
     * @param $request
     * @param $relations
     * @return Builder[]|Collection
     */
    public function getTreatedClaims($request, $relations)
    {
        $claims =  Claim::query()
            ->with($relations);

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        return $claims->whereHas('activeTreatment',function (Builder $builder) use($request){
            $builder
                ->where('validated_at', '>=', Carbon::parse($request->date_start)->startOfDay())
                ->where('validated_at', '<=', Carbon::parse($request->date_end)->endOfDay());
        })->get();

    }

    /**
     * @param $request
     * @param $relations
     * @return Builder[]|Collection
     */
    public function getTreatedInTimeClaims($request, $relations)
    {

        $claims =  $this->getTreatedClaims($request,$relations);

        $claims->filter(function ($claim){
            $treatmentDuration = Carbon::parse($claim->created_at)->diffInDays(Carbon::parse($claim->activeTreatment->validated_at));
            return $treatmentDuration<=$claim->time_limit;
        });

        return $claims;
    }

    /**
     * @param $request
     * @param $relations
     * @return Builder[]|Collection
     */
    public function getTreatedOutOfTimeClaims($request, $relations)
    {

        $claims =  $this->getTreatedClaims($request,$relations);

        $claims->filter(function ($claim){
            $treatmentDuration = Carbon::parse($claim->created_at)->diffInDays(Carbon::parse($claim->activeTreatment->validated_at));
            return $treatmentDuration>$claim->time_limit;
            });

        return $claims;
    }

    /**
     * @param $request
     * @param $relations
     * @return Builder
     */
    public function getSatisfactionMeasuredClaims($request, $relations)
    {

        $claims =  Claim::query()
            ->with($relations);

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        $claims->whereHas('activeTreatment',function (Builder $builder) use($request){
            $builder
                ->where('satisfaction_measured_at', '>=', Carbon::parse($request->date_start)->startOfDay())
                ->where('satisfaction_measured_at', '<=', Carbon::parse($request->date_end)->endOfDay());
        })->get();

        return $claims;
    }

    /**
     * @param $request
     * @param $relations
     * @return Builder
     */
    public function getPositiveSatisfactionMeasuredClaims($request, $relations)
    {
        $claims =  Claim::query()
            ->with($relations);

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        $claims->whereHas('activeTreatment',function (Builder $builder) use($request){
            $builder
                ->where('satisfaction_measured_at', '>=', Carbon::parse($request->date_start)->startOfDay())
                ->where('satisfaction_measured_at', '<=', Carbon::parse($request->date_end)->endOfDay())
                ->where('is_claimer_satisfied',true);
        })->get();

        return $claims;
    }

    /**
     * @param $request
     * @param $relations
     * @return int|string
     */
    public function getSatisfactionRate($request, $relations)
    {
        $measuredClaims = $this->getSatisfactionMeasuredClaims($request,$relations)->count();
        $positiveMeasuredClaims = $this->getPositiveSatisfactionMeasuredClaims($request,$relations)->count();

        return $measuredClaims>0?number_format(($positiveMeasuredClaims/$measuredClaims)*100,2):0;
    }

    /**
     * @param $request
     * @param $relations
     * @return int|string
     */
    public function getAverageNumberOfDaysForTreatment($request, $relations)
    {
        $treatedClaims =  $this->getTreatedClaims($request,$relations);
        $totalTreatedClaims =  $this->getTreatedClaims($request,$relations)->count();
        $totalClaimsTreatmentDuration = 0;

        foreach ($treatedClaims as $claim){
            $treatmentDuration = Carbon::parse($claim->created_at)->diffInDays(Carbon::parse($claim->activeTreatment->validated_at));
            $totalClaimsTreatmentDuration+=$treatmentDuration;
        }

        return $totalTreatedClaims>0?number_format(($totalClaimsTreatmentDuration/$totalTreatedClaims),2):0;
    }


    /**
     * @param $request
     * @return array|BuildsQueries[]|Builder[]|Collection|int
     */
    protected function getClaimsResolvedOnTime($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->whereNotNull('treatments.satisfaction_measured_at')
            ->whereRaw('DATEDIFF(validated_at,claims.created_at) < time_limit')
            ->when($request->has('unit_targeted_id'),function ($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name,units.id, count(*) as total')
                    ->groupBy('units.name','units.id')
                    ->orderByDesc('total');
            });

        return $request->has('unit_targeted_id') ? $claims->get():$claims->count();
    }


    /**
     * @param $request
     * @return array|BuildsQueries[]|Builder[]|Collection|int
     */
    protected function getClaimsResolvedLate($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->whereNotNull('treatments.satisfaction_measured_at')
            ->whereRaw('DATEDIFF(validated_at,claims.created_at) > time_limit')
            ->when($request->has('unit_targeted_id'),function ($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name,units.id, count(*) as total')
                    ->groupBy('units.name','units.id')
                    ->orderByDesc('total');
            });

        return $request->has('unit_targeted_id') ? $claims->get():$claims->count();
    }


    /**
     * @param $request
     * @return array|BuildsQueries[]|Builder[]|Collection|int
     */
    protected function getHighlyClaimsResolvedOnTime($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'claim_objects.severity_levels_id')
            ->whereNotNull('treatments.satisfaction_measured_at')
            ->whereRaw('DATEDIFF(validated_at,claims.created_at) < claims.time_limit')
            ->where('severity_levels.status','=','high')
            ->when($request->has('unit_targeted_id'),function($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name, count(*) as total')
                    ->groupBy('units.name')
                    ->orderByDesc('total');
            });


        return $request->has('unit_targeted_id') ? $claims->get():$claims->count();
    }


    /**
     * @param $request
     * @return array|BuildsQueries[]|Builder[]|Collection|int
     */
    protected function getLowMediumClaimsResolvedOnTime($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->has('unit_targeted_id')) {
            $claims->whereIn('unit_targeted_id', $request->unit_targeted_id);
        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->join('treatments', 'treatments.claim_id', '=', 'claims.id')
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'claim_objects.severity_levels_id')
            ->whereNotNull('treatments.satisfaction_measured_at')
            ->whereRaw('DATEDIFF(validated_at,claims.created_at) < claims.time_limit')
            ->where('severity_levels.status','=','low')
            ->orWhere('severity_levels.status','=','medium')
            ->when($request->has('unit_targeted_id'),function($query){
                $query->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
                    ->selectRaw('units.name,units.id, count(*) as total')
                    ->groupBy('units.name','units.id')
                    ->orderByDesc('total');
            });

        return $request->has('unit_targeted_id') ? $claims->get():$claims->count();

    }


}
