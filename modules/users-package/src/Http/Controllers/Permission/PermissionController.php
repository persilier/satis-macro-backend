<?php

namespace Satis2020\UserPackage\Http\Controllers\Permission;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\UserPackage\Http\Resources\PermissionCollection;
use Spatie\Permission\Models\Permission;
use Satis2020\UserPackage\Http\Resources\Permission as PermissionResource;
class PermissionController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-permission')->only(['index']);
        $this->middleware('permission:can-create-permission')->only(['store']);
        $this->middleware('permission:can-show-permission')->only(['show']);
        $this->middleware('permission:can-update-permission')->only(['update']);
        $this->middleware('permission:can-delete-permission')->only(['destroy']);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return PermissionCollection
     */
    public function index()
    {
        return new PermissionCollection(Permission::where('guard_name', 'api')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return PermissionResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:'.config('permission.table_names.permissions'),
        ];
        $this->validate($request, $rules);
        $permission = Permission::create(['name' => $request->name, 'guard_name' => 'api']);
        return new PermissionResource($permission);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $permission
     * @return PermissionResource
     */
    public function show($permission)
    {
        return new PermissionResource(
            Permission::where('name', $permission)->where('guard_name', 'api')->firstOrFail()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $permission
     * @return PermissionResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $permission)
    {
        $rules = [
            'name' => 'required|exists:'.config('permission.table_names.permissions'),
        ];
        $this->validate($request, $rules);
        $permission = Permission::where('name', $permission)->where('guard_name', 'api')->firstOrFail();
        $permission->name = $request->name;
        if(!$permission->isDirty()){
            return $this->errorResponse('Vous devez spécifier une valeur différente à mettre à jour', 422);
        }
        $permission->save();
        return new PermissionResource($permission);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $permission
     * @return PermissionResource
     */
    public function destroy($permission)
    {
        $permission = Permission::where('name', $permission)->where('guard_name', 'api')->firstOrFail();
        $permission->delete();
        return new PermissionResource($permission);
    }
}
