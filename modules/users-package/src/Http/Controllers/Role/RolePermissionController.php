<?php

namespace Satis2020\UserPackage\Http\Controllers\Role;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\UserPackage\Http\Resources\PermissionCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends ApiController
{

    /**
     * RolePermissionController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-assign-permissions-to-role')->only(['store']);
        $this->middleware('permission:can-revoke-permission-from-role')->only(['destroy']);
        $this->middleware('permission:can-give-all-permissions-to-role')->only(['give_all']);*/
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param string $role
     * @return PermissionCollection
     * @throws ValidationException
     */
    public function store(Request $request, $role)
    {
        $rules = [
            'permissions' => 'required|array',
        ];

        $this->validate($request, $rules);

        $role = Role::where('name', $role)->firstOrFail();

        $permissions = [];
        foreach ($request->permissions as $permission) {
            if (in_array($permission, $permissions)) {
                return $this->errorResponse("{$permission} appear more than once. Please provide a valid permissions list", 409);
            }
            $permissions[] = $permission;
            Permission::where('name', $permission)->where('guard_name', 'api')->firstOrFail();
        }

        $role->syncPermissions($request->permissions);
        return new PermissionCollection($role->permissions);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $role
     * @param string $permission
     * @return PermissionCollection
     */
    public function destroy($role, $permission)
    {
        $role = Role::where('name', $role)->where('guard_name', 'api')->firstOrFail();
        Permission::where('name', $permission)->where('guard_name', 'api')->firstOrFail();
        $role->revokePermissionTo($permission);
        return new PermissionCollection($role->permissions);
    }

    /**
     * Attribute all permissions to a Role (for development purposes)
     *
     * @param Request $request
     * @return PermissionCollection
     * @throws ValidationException
     */
    public function give_all(Request $request)
    {

        $rules = [
            'role' => 'required|exists:roles,name',
        ];

        $this->validate($request, $rules);

        $role = Role::where('name', $request->role)->firstOrFail();

        $permissions = Permission::where('guard_name', 'api')->get()->pluck('name');

        $role->syncPermissions($permissions);
        return new PermissionCollection($role->permissions);
    }


}
