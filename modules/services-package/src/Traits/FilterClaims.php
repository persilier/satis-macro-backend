<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
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
     * @param $request
     * @param array $relations
     * @return Builder
     */
    protected function getAllClaimsByPeriod($request,$relations=[]){

        $claims = Claim::query()->with($relations);

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
            ->where('created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        return $claims;
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getClaimsReceivedBySeverityLevel($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'claim_objects.severity_levels_id')
            ->selectRaw('severity_levels.name,severity_levels.id, count(*) as total')
            ->groupBy('severity_levels.name','severity_levels.id');
        
        return $claims;

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
     * @return Builder
     */
    protected function getClaimsReceivedByClaimObject($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('claim_objects', 'claim_objects.id', '=', 'claims.claim_object_id')
            ->selectRaw('claim_objects.name, count(*) as total')
            ->groupBy('claim_objects.name')
            ->orderByDesc('total');
            //->limit(3);
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
    protected function getClaimsReceivedByUnit($request){

        $claims = Claim::query();

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);

        }

        $claims->where('claims.created_at', '>=', Carbon::parse($request->date_start)->startOfDay())
               ->where('claims.created_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        $claims = $claims
            ->leftJoin('units', 'units.id', '=', 'claims.unit_targeted_id')
            ->selectRaw('units.name, count(*) as total')
            ->groupBy('units.name')
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
                ->where('satisfaction_measured_at', '<=', Carbon::parse($request->date_end)->endOfDay());

        //$claims->where('status', $status);

        return $claims;

    }

    /**
     * @param $request
     * @param $status
     * @param array $relations
     * @param bool $treatment
     * @return Builder
     */
    function getClaimsByStatus($request, $status, $relations=[], $treatment=false)
    {
        $claims = $this->getAllClaimsByPeriod($request,$relations);

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


}
