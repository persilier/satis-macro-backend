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

    protected function getAllClaimsBySemester($institution_id,$relations=[]){

        $dateStart =  Carbon::now()->startOfDay()->subMonths(6);
        $dateEnd = $dateStart->copy()->addMonths(6)->endOfDay();

        $claims = Claim::query()
            ->with($relations)
            ->where('institution_targeted_id', $institution_id);

        $claims->where('created_at', '>=', $dateStart)
            ->where('created_at', '<=', $dateEnd);

        return $claims;
    }


    function getClaimsByStatus($claims,$status,$relations=[],$treatment=false)
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


}
