<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;


/**
 * Trait ReportingClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait PilotMonitoringTrait
{

    protected function getClaimAssigned($request)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
              ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
            ->whereNotNull('transferred_to_unit_by');
        if ($request->pilot_id != Constants::ALL_PILOT) {
            $claims->where('treatments.transferred_to_unit_by', $request->pilot_id);
        }

        return $claims;
    }

    protected function getClaimValidated($request)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
              ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
            ->whereNotNull('treatments.validated_at');
        if ($request->pilot_id != Constants::ALL_PILOT) {
            $claims->where('treatments.transferred_to_unit_by', $request->pilot_id);
        }

        return $claims;
    }

    protected function getClaimSatisfied($request)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
              ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
            ->whereNotNull('treatments.satisfaction_measured_at');
        if ($request->pilot_id != Constants::ALL_PILOT) {
            $claims->where('treatments.transferred_to_unit_by', $request->pilot_id);
        }

        return $claims;
    }

    protected function getClaimRejected($request)
    {
        $claims = Claim::query();
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }
        $claims->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
              ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
            ->whereNotNull('treatments.rejected_at');
        if ($request->pilot_id != Constants::ALL_PILOT) {
            $claims->where('treatments.transferred_to_unit_by', $request->pilot_id);
        }

        return $claims;
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getAverageTimeOfAssignation($request)
    {
        $claimAssigned = $this->getClaimAssigned($request);


        $i = 0;
        $totalTime = 0;
        if ($claimAssigned->count() == 0) {
            $averageTime = 0;
        } else {
            $claimAssigned = $claimAssigned->get();
            foreach ($claimAssigned as $value) {

                $i++;
                $totalTime +=  $value->timeLimitUnit['time_done_in_minutes'];
            }

            $averageTime = $totalTime / $i;

        }


        return conversionToDayHourMinute($averageTime);
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getAverageTimeOfValidation($request)
    {
        $claimValidated = $this->getClaimValidated($request);

        $i = 0;
        $totalTime = 0;

        if ($claimValidated->count() == 0) {
            $averageTime = 0;
        } else {
            $claimValidated = $claimValidated->get();
            foreach ($claimValidated as $value) {
                $i++;
                $totalTime +=  $value->timeLimitValidation['time_done_in_minutes'];
            }

            $averageTime = $totalTime / $i;
        }


        return conversionToDayHourMinute($averageTime);
    }

    /**
     * @param $request
     * @return Builder
     */
    protected function getAverageTimeOfSatisfaction($request)
    {
        $claimSatisfied =  $this->getClaimSatisfied($request);


        $i = 0;
        $totalTime = 0;
        if ($claimSatisfied->count() == 0) {
            $averageTime = 0;
        } else {

            $claimSatisfied = $claimSatisfied->get();
            foreach ($claimSatisfied as $value) {
                $i++;
                $totalTime +=  $value->timeLimitMeasureSatisfaction['time_done_in_minutes'];
            }

            $averageTime = $totalTime / $i;
        }


        return conversionToDayHourMinute($averageTime);
    }





    /**
     * @param $request
     * @param int $paginationSize
     * @param null $type
     * @param null $key
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */

    protected function getPilotClaimAssignedTo($request, $paginationSize = 10, $type = null, $key = null)
    {

        $claims = Claim::query()->with($this->getRelations());
        if ($request->has('institution_id')) {
            $claims->where('institution_targeted_id', $request->institution_id);
        }

        $claims->select('claims.*')->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
              ->on('claims.active_treatment_id', '=', 'treatments.id');
        })
            ->whereNotNull('transferred_to_unit_by');
        

        if ($request->pilot_id != Constants::ALL_PILOT) {
            $claims->where('treatments.transferred_to_unit_by', $request->pilot_id);
        }

        if ($request->status) {

            if ($request->status == "assigned") {

                $claims = $claims;
            }
            if ($request->status == "validated") {

                $claims = $claims->whereNotNull('treatments.validated_at');
            }
            if ($request->status == "surveyed") {

                $claims = $claims->whereNotNull('treatments.satisfaction_measured_at');
            }
        }

        if ($key) {
            switch ($key) {
                case 'reference':
                    $claims = $claims->where('reference', 'LIKE', "%$key%");
                    break;
                case 'claimObject':
                    $claims = $claims->whereHas("claimObject", function ($query) use ($key) {
                        $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    });
                    break;
                default:
                    $claims = $claims->whereHas("claimer", function ($query) use ($key) {
                        $query->where('firstname', 'like', "%$key%")
                            ->orWhere('lastname', 'like', "%$key%")
                            ->orwhereJsonContains('telephone', $key)
                            ->orwhereJsonContains('email', $key);
                    });
                    break;
            }
        }

        return $claims->paginate($paginationSize);
    }
}
