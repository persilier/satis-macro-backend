<?php


namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionType;

trait InstitutionTrait
{
    protected  function getMaximumInstitution($type_id, $nature){
        $message = "Unable to find the user institution";
        try {
            $maxInstitution = InstitutionType::where('id', $type_id)->where('application_type',$nature)->firstOrFail()->maximum_number_of_institutions;
            return $maxInstitution;
        } catch (\Exception $exception) {
            throw new CustomException($message);
        }

    }

    protected function getVerifiedStore($type_id, $nature){
        $message = "Unable to find the user institution";
        try {
            $max = $this->getMaximumInstitution($type_id, $nature);
        } catch (CustomException $e) {
            throw new CustomException($message);
        }
        if($max == 0){
            return true;
        }
        $number = Institution::where('institution_type_id', $type_id)->get()->count('id');
        if($max > $number)
            return true;
        else
            return false;
    }

    protected function getOneMyInstitution($institution, $institution_id){

        $message = "Unable to find the user institution";

        try {
            $institution = Institution::with('institutionType')->findOrFail($institution);
        } catch (\Exception $exception) {
            throw new CustomException($message);
        }

        if (is_null($institution)) {
            throw new CustomException($message);
        }

        if ($institution != $institution_id) {
            throw new CustomException($message);
        }

        return $institution;
    }

}