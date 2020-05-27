<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\UserPackage\Http\Resources\User as UserResource;
class AuthController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->user = Auth::user();
        $data = $this->getInstitutionStaff($this->user->id);
        $this->institution = $data['institution'];
        $this->staff = $data['staff'];
    }

    /**
     * Log the user into the application
     *
     * @return UserResource
     */
    public function login()
    {
        $user = $this->user;

        return (new UserResource($user))->additional([
            "app-nature" => $this->nature,
            "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
            'institution'=> $this->institution
        ]);
    }

    /**
     * Log the user out the application
     *
     * @return Response
     */
    public function logout()
    {
        $this->user->token()->revoke();
        return $this->showMessage('Déconnexion réussie de l\'utilisateur');
    }

}