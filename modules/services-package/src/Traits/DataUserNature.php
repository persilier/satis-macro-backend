<?php


namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;

trait DataUserNature
{

    protected function getIdentiteStaff($user_id){
        $user = User::with('identite.staff')->where($user_id)->first();
        if (!is_null($user) && !is_null($user->identite->staff)) {
            return [
                'status' => true,
                'staff' => $user->identite->staff,
                'message' => ''
            ];
        }
        return [
            'status' => false,
            'staff' => '',
            'message' => 'L\'utilisateur connecté n\'est pas un Staff',
        ];
    }

    protected function getInstitutionStaff($user_id)
    {
        $staff = $this->getIdentiteStaff($user_id);
        if(true == $staff['status']){
            $institution = Institution::with('institutionType')->find($staff['staff']->id);
            if (!is_null($institution)) {
                return [
                    'status' => false,
                    'institution' => $institution,
                    'staff' => $staff['staff']
                ];
            }
        }
        return [
            'status' => false,
            'institution' => '',
            'message' => 'L\'utilisateur connecté n\'est dans aucune institution',
        ];
    }

    public function getNatureApp(){
        return $app_nature = json_decode(Metadata::where('name', 'app-nature')->firstOrFail()->data);
    }

}