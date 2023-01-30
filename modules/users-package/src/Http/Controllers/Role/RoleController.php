<?php

namespace Satis2020\UserPackage\Http\Controllers\Role;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Satis2020\UserPackage\Http\Resources\Role as RoleResource;
use Satis2020\UserPackage\Http\Resources\RoleCollection;
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

    /**
     * Display a listing of the resource.
     *
     * @return RoleCollection
     */
    public function index()
    {
        return new RoleCollection(Role::where('guard_name', 'api')->get());

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RoleResource
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:'.config('permission.table_names.roles'),
            'description'=>'required|string',
        ];
        $this->validate($request, $rules);
        $role = Role::create(['name' => $request->name, 'guard_name' => 'api','description'=>$request->description]);
        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $role
     * @return RoleResource
     */
    public function show($role)
    {
        return new RoleResource(
            Role::where('name', $role)->where('guard_name', 'api')->with('permissions')->firstOrFail()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $role
     * @return RoleResource
     * @throws ValidationException
     */
    public function update(Request $request, $role)
    {
        $rules = [
            'name' => 'required|unique:'.config('permission.table_names.roles'),
            'description'=>'required|string',
        ];

        $this->validate($request, $rules);

        $role = Role::where('name', $role)->where('guard_name', 'api')->firstOrFail();

        $role->name = $request->name;
        $role->description = $request->description;

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
