<?php
namespace Satis2020\AnyUser\Http\Controllers\User;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\UserTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
/**
 * Class UserController
 * @package Satis2020\UserPackage\Http\Controllers\User
 */
class UserController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, UserTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-user-any-institution')->only(['index']);
        $this->middleware('permission:store-user-any-institution')->only(['create','store']);
        $this->middleware('permission:show-user-any-institution')->only(['show']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {

        $users = $this->getAllUser();

        return response()->json($users,200);
    }


    /**
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user)
    {
        return response()->json($this->getOneUser($user),200);
    }


    /**
     * @return JsonResponse
     */
    public function create(){

        return response()->json(Institution::all(),200);

    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {

        $this->validate($request, $this->rulesCreateUser());

        $identiteRole = $this->verifiedRoleTypeInstitution($request);

        $user = $this->storeUser($request, $identiteRole);

        return response()->json($user,201);
    }


    /**
     * @param Request $request
     * @param User $user
     */
    protected function changePassword(Request $request, User $user){

        $request->validate([

            'current_password' => ['required'],
            'new_password' => ['required'],
            'password_confirmation' => ['same:new_password'],

        ]);

        //User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);

    }

}
