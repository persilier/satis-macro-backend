<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\PasswordReset;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Notifications\PasswordResetRequest;
use Satis2020\ServicePackage\Notifications\PasswordResetSuccess;
use Satis2020\ServicePackage\Rules\IsValidPasswordRules;
use Satis2020\ServicePackage\Traits\ApiResponser;

/**
 * Class PasswordResetController
 * @package Satis2020\UserPackage\Http\Controllers\Auth
 */
class PasswordResetController extends ApiController{
    use ApiResponser;

    public function __construct()
    {
        //parent::__construct();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([ 'email' => 'required|string|email']);
        $requestParams = [];

        if (!$user = User::where('username', $request->email)->first()){

            return $this->errorResponse(__('passwords.user'), 404);

        }

        $passwordReset = PasswordReset::updateOrCreate(['email' => $user->username], [

            'email' => $user->username,
            'token' => Str::random(80)
        ]);

        $requestParams['origin'] = $request->headers->get('origin');

        if ($user && $passwordReset){
            $user->notify(new PasswordResetRequest($passwordReset->token, $requestParams ));
        }

        return response()->json([
            'message' => __('passwords.sent'),
            'code' => 200
        ]);
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function find($token)
    {

        if (!$passwordReset = PasswordReset::where('token', $token)->first()){

            return $this->errorResponse( __('passwords.token'), 404);
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return $this->errorResponse( __('passwords.token'), 404);
        }

        return response()->json([
            'data' => $passwordReset,
            'code' => 200
        ]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => ['required','confirmed', 'string', new IsValidPasswordRules],
            'token' => 'required|string'
        ]);


        if (!$passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first()){

            return $this->errorResponse( __('passwords.token'), 404);
        }


        if (!$user = User::where('username', $passwordReset->email)->first()){


            return $this->errorResponse( __('passwords.token'), 404);
        }

        $user->forceFill(['password' => Hash::make($request->password)])->save();

        $passwordReset->delete();

        $user->notify(new PasswordResetSuccess($passwordReset));

        return response()->json([
            'user' => $user,
            'message' => 'Mot de passe réinitialisé avec succès.',
            'code' => 201
        ]);
    }


}
