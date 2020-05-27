<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\UserPackage\Http\Resources\User as UserResource;
class AuthController extends ApiController
{
    use DataUserNature;
    protected $user;
    protected $institution;

    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->verified($this->user->id);
        dd($this->user);
        $this->middleware('auth:api');
    }

    protected function verified($user_id){
        $verifiedInstitution = $this->getInstitutionStaff($user_id);
        if(true == $verifiedInstitution['status']){
            return $this->institution = $verifiedInstitution['institution'];
        }
        return $this->showMessage($verifiedInstitution['message']);
    }

    /**
     * Log the user into the application
     *
     * @return UserResource
     */
    public function login()
    {
        /*$user = Auth::user();

        return (new UserResource($user))->additional([
            "app-nature" => $this->getNatureApp(),
            "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
            'institution'=> $this->getInstitution($user->identite->staff->institution_id)
        ]);*/
    }

    /**
     * Log the user out the application
     *
     * @return Response
     */
    public function logout()
    {
        Auth::user()->token()->revoke();
        return $this->showMessage('Déconnexion réussie de l\'utilisateur');
    }

}