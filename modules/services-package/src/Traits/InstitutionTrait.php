<?php


namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionType;

trait InstitutionTrait
{
    protected  function getMaximumInstitution($type_id, $nature){
        $maxInstitution = InstitutionType::where('id', $type_id)->where('application_type',$nature)->firstOrFail()->maximum_number_of_institutions;
        return $maxInstitution;
    }

    protected function getVerifiedStore($type_id, $nature){
        $max = $this->getMaximumInstitution($type_id, $nature);
        if($max == 0){
            return true;
        }
        $number = Institution::where('institution_type_id', $type_id)->get()->count('id');
        if($max > $number)
            return true;
        else
            return false;
    }


    protected function getOneInstitutionByType($institution, $user_institution, $user_institution_type){
        $institution = Institution::with('institutionType')->findOrFail($institution);
        if(($user_institution_type == 'holding') || ($user_institution_type == 'observatoire')) {
            return [
                'status' => true,
                'institution' => $institution,
                'message' => ''
            ];
        }
        if($institution->id == $user_institution){
            return [
                'status' => true,
                'institution' => $institution,
                'message' => ''
            ];
        }

        return [
            'status' => false,
            'institution' => '',
            'message' => ''
        ];
    }

    protected function getInstitutionByType($institution, $user_institution, $user_institution_type){
        $institution = $this->getOneInstitutionByType($institution, $user_institution, $user_institution_type);
        if(true == $institution['status'])
            return $institution['institution'];
        return null;
    }

    protected function getAllInstitutionByType($user_institution_type){
        if(($user_institution_type == 'holding') || ($user_institution_type == 'observatoire')) {
            return [
                $institutions = Institution::with('institutionType')->get()
            ];
        }
        return null;
    }

    public function errorMessageGetInstitution(){
        return response()->json(['error'=> "Does not exist institution with the specified identificator.", 'code' => 404], 404);
    }

}