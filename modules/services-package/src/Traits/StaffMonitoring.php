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
trait StaffMonitoring
{

    /**
     * @param $request
     * @param $unitId
     * @return Builder
     */
    protected function getClaimAssigned($request,$unitId){
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', 'treatments.claim_id', '=', 'claims.id')
               ->where('responsible_unit_id', $unitId);
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        $claims = $claims->whereNotNull('treatments.assigned_to_staff_at')
                         ->whereNull('claims.deleted_at');
        return $claims;
    }


    /**
     * @param $request
     * @param $unitId
     * @return Builder
     */
    protected function getClaimTreated($request,$unitId){
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', 'treatments.claim_id', '=', 'claims.id')
               ->where('responsible_unit_id', $unitId);
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        $claims = $claims->whereNotNull('treatments.assigned_to_staff_at')
                         ->whereNotNull('treatments.solved_at')
                         ->whereNull('claims.deleted_at');
        return $claims;
    }



    /**
     * @param $request
     * @param $unitId
     * @return Builder
     */
    protected function getClaimNoTreated($request,$unitId){
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', 'treatments.claim_id', '=', 'claims.id')
               ->where('responsible_unit_id', $unitId);
        if ($request->staff_id != Constants::ALL_STAFF) {
            $claims->where('treatments.responsible_staff_id', $request->staff_id);
        }
        $claims = $claims->whereNotNull('treatments.transferred_to_unit_at')
                         ->whereNotNull('treatments.assigned_to_staff_at')
                         ->whereNull('treatments.solved_at')
                         ->whereNull('claims.deleted_at');
        return $claims;
    }


    /**
     * @param $request
     * @param $unitId
     * @param int $paginationSize
     * @param null $type
     * @param null $key
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */

    protected function getAllStaffClaim($request, $unitId, $paginationSize = 10, $type = null, $key = null){

    $claims =  Claim::query()->with($this->getRelations())
        ->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id')
                ->whereNotNull('treatments.transferred_to_unit_at');
        })->where('responsible_unit_id', $unitId)
        ->whereNotNull('treatments.assigned_to_staff_at')
        ->whereNull('claims.deleted_at');

    if($key){
        if ($type == 'reference') {
            $claims->where('reference', 'LIKE', "%$key%");
        } elseif ($type == 'claimObject'){
            $claims->whereHas("claimObject",function ($query) use ($key){
                $query->where("name->".App::getLocale(), 'LIKE', "%$key%");
            });
        } elseif ($type == 'claimer'){
            $claims->whereHas("claimer",function ($query) use ($key){
                $query->where('firstname' , 'like', "%$key%")
                    ->orWhere('lastname' , 'like', "%$key%")
                    ->orwhereJsonContains('telephone', $key)
                    ->orwhereJsonContains('email', $key);
            });
        }
    }

    if ($request->has('institution_id')){
        $claims->where('institution_targeted_id', $request->institution_id);
    }
    if ($request->staff_id != Constants::ALL_STAFF){
        $claims->where('treatments.responsible_staff_id', $request->staff_id);
    }

    $claims = $claims->paginate($paginationSize);

    return $claims;

}

}
