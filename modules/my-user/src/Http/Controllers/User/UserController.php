<?php

namespace Satis2020\MyUser\Http\Controllers\User;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Traits\CheckInactivityDuration;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\UserTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class UserController
 * @package Satis2020\UserPackage\Http\Controllers\User
 */
class UserController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, UserTrait, CheckInactivityDuration, Metadata;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-user-my-institution')->only(['index']);
        $this->middleware('permission:store-user-my-institution')->only(['create', 'store']);
        $this->middleware('permission:show-user-my-institution')->only(['show', 'getUserUpdate', 'enabledDesabled', 'userUpdate']);
    }


    public function index()
    {
        $pagination = \request()->query('size');
        $search_text = \request()->query('search_text');
        $users = $this->getAllUser(true, request('size', 10), $search_text);
        return response()->json($users, 200);
    }


    /**
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user)
    {
        return response()->json($this->getOneUser($user, true), 200);
    }


    /**
     * @return JsonResponse
     */
    public function create()
    {

        return response()->json($this->getAllIdentitesRoles($this->institution()), 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $institution = $this->institution();

        $this->validate($request, $this->rulesCreateUser(false));

        $request->merge(['institution_id' => $institution->id]);

        $identiteRole = $this->verifiedRoleTypeInstitution($request);

        $user = $this->storeUser($request, $identiteRole);

        return response()->json($user, 201);
    }


    /**
     * @param User $user
     * @return JsonResponse
     */
    protected function getUserUpdate(User $user)
    {

        return response()->json([
            'user' => $this->getOneUser($user, true),
            'roles' => $this->getAllRolesInstitutionUser($user, true)
        ]);
    }


    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     * @throws ValidationException
     */
    protected function userUpdate(Request $request, User $user)
    {

        $this->validate($request, $this->rulesCreateUser(false, true));

        if ($request->filled('roles')) {
            $roles = $this->verifiedRole($request, $user->identite);
            $user = $this->remokeAssigneRole($user, $roles);
        }

        if ($request->filled('new_password')) {

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
    protected function changePassword(Request $request, User $user)
    {

        $this->myUser($user);

        $this->validate($request, $this->rulesChangePassword());

        return response()->json($this->updatePassword($request, $user), 201);
    }


    /**
     * @param User $user
     * @return JsonResponse
     */
    protected function enabledDesabled(User $user)
    {

        return response()->json($this->statusUser($user), 201);
    }
}
