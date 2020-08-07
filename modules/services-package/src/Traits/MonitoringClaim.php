<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Trait MonitoringClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait MonitoringClaim
{
    /**
     * @param $request
     * @param $status
     * @param bool $treatment
     * @return mixed
     */
    protected function getAllClaim($request , $status, $treatment = false)
    {
        try {

            $claims = $this->getAllDataFilter($request, $status, $treatment)->map(function ($item) {
                $item['time_expire'] = $this->timeExpire($item->created_at, $item->claimObject->time_limit);
                return $item;
            });

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de récupérer des réclamations.");
        }

        return $claims;
    }


    /**
     * @param $createdDate
     * @param $timeLimit
     * @return mixed
     */
    protected function timeExpire($createdDate = false , $timeLimit = false){

        $diff = null;

        if($timeLimit && $createdDate){

            $dateExpire = $createdDate->addDays($timeLimit);
            $diff = now()->diffInDays(($dateExpire), false);
        }

        return $diff;
    }


    /**
     * @param $request
     * @param $status
     * @param bool $treatment
     * @return Builder
     */
    protected function getAllDataFilter($request , $status, $treatment)
    {
        $claims = Claim::with($this->getRelations());

        if($request->has('institution_id')){

            $claims->where('institution_targeted_id', $request->institution_id);

        }


        if($treatment){

            $claims->join('treatments', function ($join) {

                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })->select('claims.*');
        }


        if($status === 'transferred_to_targeted_institution'){

            $claims->where('status', 'full')->orWhere('status', 'transferred_to_targeted_institution');

        }else{

            $claims->where('status', $status);
        }


        return $claims->get();
    }


    /**
     * @param $claimId
     * @param bool $institutionId
     * @return Builder|Builder[]|Collection|Model
     */
    protected function getOne($claimId, $institutionId = false)
    {

        $claim = Claim::with($this->getRelations())->findOrFail($claimId);

        if($institutionId){
            if($institutionId != $claim->institution_targeted_id)
                throw new CustomException("Impossible de récupérer des réclamations.");
        }

        $time_limit =  $this->timeExpire($claim->created_at, $claim->claimObject->time_limit);

        $claim = collect($claim)->toArray();

        $claim['time_expire'] = $time_limit;

        return $claim;
    }


    /**
     * @return array
     */
    protected function getRelations()
    {
        $relations = [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment.responsibleStaff',
             'activeTreatment.responsibleUnit'
        ];

        return $relations;
    }

    /**
     * @param $request
     * @param bool $institutionId
     * @return array
     */
    protected function rules($request, $institutionId = false)
    {

        $data = [
            'institution_id' => 'sometimes|exists:institutions,id',
            'claim_category_id' => 'sometimes|exists:claim_categories,id',
            'claim_object_id' => 'sometimes|', Rule::exists('claim_objects', 'id')->where(function ($query) use ($request) {
                $query->where('id', $request->claim_category_id);
            }),
            'unit_id' => 'sometimes|', Rule::exists('units', 'id')->where(function ($query) use ($request) {
                $query->where('id', $request->unit_id)->where('institution_id', $request->institution_id);
            }),
            'staff_id' => 'sometimes|', Rule::exists('staff', 'id')->where(function ($query) use ($request) {
                $query->where('id', $request->staff_id)->where('institution_id', $request->institution_id);
            }),
            'date_start' => 'sometimes|date_format:Y-m-d',
            'date_end' => 'sometimes|date_format:Y-m-d|after:date_start'
        ];

        return $data;
    }

    /**
     * @param $incompletes
     * @param $toAssignedToUnit
     * @param $toAssignedToUStaff
     * @param $awaitingTreatment
     * @param $toValidate
     * @param $toMeasureSatisfaction
     * @param bool $institutionId
     * @return array
     */
    protected function metaData($incompletes , $toAssignedToUnit , $toAssignedToUStaff,$awaitingTreatment, $toValidate, $toMeasureSatisfaction, $institutionId = false){

        $data = [
            'incompletes' =>  $incompletes,
            'toAssignementToUnit' => $toAssignedToUnit,
            'toAssignementToStaff' => $toAssignedToUStaff,
            'awaitingTreatment' => $awaitingTreatment,
            'toValidate' => $toValidate,
            'toMeasureSatisfaction' => $toMeasureSatisfaction,
            'claimCategories' => ClaimCategory::all(),
            'claimObjects' => ClaimObject::all(),
        ];

        if($institutionId){

            $data['units'] = Unit::where('institution_id',$institutionId)->get();
            $data['staffs'] = Staff::with('identite')->where('institution_id',$institutionId)->get();

        }else{

            $data['institutions'] = Institution::all();
            $data['units'] = Unit::all();
            $data['staffs'] = Staff::with('identite')->get();
        }

        return $data;
    }



    protected function treamentRelance($claim){

        if($claim->status === 'incomplete'){

            dd($claim);

        }

        if($claim->status === 'full'){

        }

        if($claim->status === 'transferred_to_targeted_institution'){

        }

        if($claim->status === 'transferred_to_unit'){

        }

        if($claim->status === 'assigned_to_staff'){

        }

        if($claim->status === 'treated'){

        }

        if($claim->status === 'validated'){

        }

    }


}