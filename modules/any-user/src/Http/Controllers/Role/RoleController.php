<?php

namespace Satis2020\AnyUser\Http\Controllers\Role;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Class RoleController
 * @package Satis2020\AnyUser\Http\Controllers\Role
 */
class RoleController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-role')->only(['index']);
        $this->middleware('permission:can-create-role')->only(['store']);
        $this->middleware('permission:can-update-role')->only(['update']);
        $this->middleware('permission:can-show-role')->only(['show']);
        $this->middleware('permission:can-delete-role')->only(['destroy']);*/
    }


    public function index()
    {

        $roles = Role::where('guard_name', 'api')->get();
        return response()->json($roles, 200);

    }


    public function store(Request $request)
    {
        $rules = [

            'name' => 'required|unique:'.config('permission.table_names.roles'),
        ];

        $this->validate($request, $rules);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'api']);

        return response()->json($role, 200);
    }


    public function show($role)
    {
        return new RoleResource(
            Role::where('name', $role)->where('guard_name', 'api')->with('permissions')->firstOrFail()
        );
    }


    public function update(Request $request, $role)
    {
        $rules = [
            'name' => 'required|unique:'.config('permission.table_names.roles'),
        ];

        $this->validate($request, $rules);

        $role = Role::where('name', $role)->where('guard_name', 'api')->firstOrFail();

        $role->name = $request->name;

        if(! $role->isDirty()){

            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        $role->save();
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $role
     * @return RoleResource
     */
    public function destroy($role)
    {
        $role = Role::where('name', $role)->where('guard_name', 'api')->firstOrFail();
        $role->delete();
        return new RoleResource($role);
    }
}
