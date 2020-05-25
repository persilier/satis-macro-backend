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

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log the user into the application
     *
     * @return UserResource
     */
    public function login()
    {
        $user = Auth::user();
        return (new UserResource($user))->additional([
            "app-nature" => $this->getNatureApp(),
            "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
            'institution'=> $this->getInstitution($user->identite->staff->institution_id)
        ]);
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