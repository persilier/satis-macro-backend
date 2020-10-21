<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Http\Response;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class AuthController
 * @package Satis2020\UserPackage\Http\Controllers\Auth
 */
class AuthController extends ApiController
{
    use VerifyUnicity, IdentityManagement;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
    }

    /**
     * Log the user into the application
     *
     * @return UserResource
     */
    public function login()
    {
        $user = $this->user();

        return response()->json([
            'data' => $user->load('identite', 'roles'),
            'staff' => $this->staff(),
            "app-nature" => $this->nature(),
            "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
            'institution'=> $this->institution()
        ],200);

        //return response()->json(,200);

        /*return (new UserResource($user))->additional([
            'staff' => $this->staff(),
            "app-nature" => $this->nature(),
            "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
            'institution'=> $this->institution()
        ]);*/
    }

    /**
     * Log the user out the application
     *
     * @return Response
     */
    public function logout()
    {
        $this->user()->token()->revoke();
        return $this->showMessage('Déconnexion réussie de l\'utilisateur.');
    }

}
