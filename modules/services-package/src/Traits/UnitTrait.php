<?php


namespace Satis2020\ServicePackage\Traits;
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
        $unit = Unit::with([
            'unitType', 'institution', 'parent', 'lead'
        ])->where('institution_id', $institution)->findOrFail($id);
        return $unit;
    }


}