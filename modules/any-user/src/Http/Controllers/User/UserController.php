<?php
namespace Satis2020\AnyUser\Http\Controllers\User;

use Exception;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        /*$this->middleware('permission:list-user-any-institution')->only(['index']);
        $this->middleware('permission:store-user-any-institution')->only(['create','store']);
        $this->middleware('permission:show-user-any-institution')->only(['show', 'getUserUpdate', 'enabledDesabled', 'userUpdate']);*/
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
     * @param User $user
     * @return JsonResponse
     */
    protected function getUserUpdate(User $user){

        return response()->json([
            'user' => $this->getOneUser($user),
            'roles' => $this->getAllRolesInstitutionUser($user)
        ]);
    }


    /**
     * @param Request $request
     * @param User $user
     * @return User
     * @throws ValidationException
     */
    protected function userUpdate(Request $request, User $user){

        $this->validate($request, $this->rulesCreateUser(false, true));

        $roles = $this->verifiedRole($request, $user->identite);
        $user = $this->remokeAssigneRole($user, $roles);

        if($request->filled('new_password')){

            $user = $this->updatePassword($request, $user);
        }

        return response()->json($user, 201);

    }


    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     * @throws ValidationException
     */
    protected function changePassword(Request $request, User $user){

        $this->validate($request, $this->rulesChangePassword());

        return response()->json($this->updatePassword($request, $user),201);
    }


    /**
     * @param User $user
     * @return JsonResponse
     */
    protected function enabledDesabled(User $user){

        return response()->json($this->statusUser($user), 201);
    }

}
