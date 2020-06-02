<?php


namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Unit;

trait UnitTrait
{
    protected  function getAllUnitByInstitution($institution){
        $unit = Unit::with([
            'unitType', 'institution', 'parent', 'lead'
        ])->where('institution_id', $institution)->get();
        return $unit;
    }

    protected  function getOneUnitByInstitution($institution, $id){
        try{
            $unit = Unit::with([
                'unitType', 'institution', 'parent', 'lead'
            ])->where('institution_id', $institution)->findOrFail($id);
            return $unit;
        }catch (\Exception $exception){
            throw new CustomException("Can't retrieve the staff institution");
        }
    }


}