<?php


namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Trait UnitTrait
 * @package Satis2020\ServicePackage\Traits
 */
trait UnitTrait
{
    /**
     * @param $institution
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected  function getAllUnitByInstitution($institution){

        $unit = Unit::with([
            'unitType', 'institution', 'parent', 'children', 'lead.identite','state.country'
        ])->where('institution_id', $institution)->get();

        return $unit;
    }

    /**
     * @param $institution
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected  function getAllUnit(){

        $unit = Unit::with([
            'unitType', 'institution', 'parent', 'children', 'lead.identite','state.country'
        ])->get();

        return $unit;
    }

    /**
     * @param $institution
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    protected  function getOneUnitByInstitution($institution, $id){

        try{

            $unit = Unit::with([
                'unitType', 'institution', 'parent', 'children', 'lead.identite','state.country'
            ])->where('institution_id', $institution)->findOrFail($id);

            return $unit;

        }catch (\Exception $exception){
            throw new CustomException("Can't retrieve the staff institution");
        }
    }


    protected function UnitHasAnStaff($request, $unit){

        if($request->institution_id != $unit->institution_id){

            if($unit->staffs->count() > 0){

                throw new CustomException("Impossible de modifier l'institution de cette unité.");

            }

        }

    }

    protected function getUnitLeadId($unitId)
    {
        return Unit::query()->find($unitId)? Unit::query()->find($unitId)->lead_id:null;
    }

    protected function staffIsUnitLead($staff)
    {
        return $staff->is_lead;
    }


}
