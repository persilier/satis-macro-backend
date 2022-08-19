<?php

namespace Satis2020\UserPackage\Http\Controllers\User;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\User;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Satis2020\UserPackage\Http\Resources\Role as RoleResource;
class UserRoleController extends ApiController
{
    /**
     * UserUserController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-my-institution-type-role')->only(['index']);
        $this->middleware('permission:store-my-institution-type-role')->only(['store']);
        //$this->middleware('permission:show-my-institution-type-role')->only(['show']);
        //$this->middleware('permission:update-my-institution-type-role')->only(['update']);
        //$this->middleware('permission:destroy-my-institution-type-role')->only(['destroy']);

    }

    /**
     * Display a listing of the resource.
     *
     * @param  User  $user
     * @return RoleResource
     */
    public function index(User $user)
    {
        return response()->json($user->roles, -200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return RoleResource
     * @throws ValidationException
     */
    public function store(Request $request, User $user)
    {
        $rules = [
            'role' => 'required',
        ];

        $this->validate($request, $rules);

        $role = Role::where('name', $request->role)->where('guard_name', 'api')->firstOrFail();
        $user->assignRole($role);
        return response()->json($user->roles, 201);
    }

}
