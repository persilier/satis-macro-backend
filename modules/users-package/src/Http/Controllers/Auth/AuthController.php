<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Rules\EmailValidationRules;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Satis2020\UserPackage\Http\Resources\User as UserResource;

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

        return (new UserResource($user))->additional([
            'staff' => $this->staff(),
            "app-nature" => $this->nature(),
            "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
            'institution'=> $this->institution()
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function edit(){

        return response()->json($this->user()->load('identite'), 200);

    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request){

        $this->validate($request, $this->rulesProfile());

        $identite = $this->user()->identite;

        // Identity PhoneNumber Unicity Verification
        $verifyPhone = $this->handleInArrayUnicityVerification($request->telephone, 'identites', 'telephone', 'id', $identite->id);

        if (!$verifyPhone['status']) {

            $verifyPhone['message'] = 'We found someone with the phone number : ' . $verifyPhone['conflictValue'] . ' that you provide! Please, verify if it\'s the same that you want to register as the claimer';
            throw new CustomException($verifyPhone, 409);
        }

        // Identity Email Unicity Verification
        $verifyEmail = $this->handleInArrayUnicityVerification($request->email, 'identites', 'email', 'id', $identite->id);

        if (!$verifyEmail['status']) {

            $verifyEmail['message'] = 'We found someone with the email address : ' . $verifyEmail['conflictValue'] . ' that you provide! Please, verify if it\'s the same that you want to register as the claimer';
            throw new CustomException($verifyEmail, 409);
        }

        $this->updateIdentity($request, $identite);

        return response()->json($identite, 200);
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
