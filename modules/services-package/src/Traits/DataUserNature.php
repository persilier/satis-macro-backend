<?php


namespace Satis2020\ServicePackage\Traits;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;
trait DataUserNature
{
    protected $nature;
    protected $user;
    protected $institution;
    protected $staff;

    protected function user(){
        return $this->user = Auth::user();
    }

    protected function institution(){
        return $this->institution = $this->getInstitution($this->user()->id);
    }

    protected function staff(){
        return $this->staff;
    }

    protected function nature(){
        return $this->nature = $this->getNatureApp();
    }

    protected function getIdentiteStaff($user_id){
        $user = User::with('identite.staff')->findOrFail($user_id);
        if (!is_null($user->identite->staff)) {
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

    protected function getInstitution($user_id)
    {
        $staff = $this->getIdentiteStaff($user_id);
        if(true == $staff['status']){
            $institution = Institution::with('institutionType')->findOrFail($staff['staff']->institution_id);
            return $institution;
        }
    }

    protected function getNatureApp(){
        return $app_nature = json_decode(Metadata::where('name', 'app-nature')->firstOrFail()->data);
    }

}