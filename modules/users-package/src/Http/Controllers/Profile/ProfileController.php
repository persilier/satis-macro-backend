<?php
namespace Satis2020\UserPackage\Http\Controllers\Profile;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Rules\MatchOldPassword;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\VerifyUnicity;


/**
 * Class ProfileController
 * @package Satis2020\UserPackage\Http\Controllers\Profile
 */
class ProfileController extends ApiController
{
    use VerifyUnicity, IdentityManagement;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
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

        return response()->json($identite, 201);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    protected function changePassword(Request $request){

        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required'
        ]);

        $user = $this->user();

        $user->update(['password'=> bcrypt($request->new_password)]);

        return response()->json($user->load('identite'),201);
    }

}
