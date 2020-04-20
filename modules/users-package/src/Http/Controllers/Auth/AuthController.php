<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
class AuthController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log the user into the application
     *
     * @return Response
     */
    public function login()
    {
        $user = Auth::user();
        return $this->showAll(
            collect([
                "user" => collect($user),
                "identite" => collect($user->load('identite'))->only(['identite'])['identite'],
                "role" => collect($user->roles->first())->except(['pivot']),
                "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
                //"menu" => $this->getMenus()
            ])
        );
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