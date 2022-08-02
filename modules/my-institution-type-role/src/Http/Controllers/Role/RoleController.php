<?php

namespace Satis2020\MyInstitutionTypeRole\Http\Controllers\Role;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Traits\RoleTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Class RoleController
 * @package Satis2020\AnyInstitutionTypeRole\Http\Controllers\Role
 */
class RoleController extends ApiController
{
    use RoleTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-my-institution-type-role')->only(['index']);
        $this->middleware('permission:show-my-institution-type-role')->only(['show']);
        $this->middleware('permission:store-my-institution-type-role')->only(['create', 'store']);
        $this->middleware('permission:update-my-institution-type-role')->only(['edit', 'update']);
        $this->middleware('permission:destroy-my-institution-type-role')->only(['destroy']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Role::where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->get(),200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request){

        $request->merge(['institutionTypes' => ['independant']]);
        return response()->json($this->getAllPermissions($request),200);

    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->merge(['institutionTypes' => ['independant']]);

        $this->validate($request, $this->rule());

        $this->verifiedStore($request);

        return response()->json($this->createRole($request), 201);

    }


    /**
     * @param $role
     * @return JsonResponse
     */
    public function show($role)
    {

        return response()->json($this->getRole($role),200);

    }


    /**
     * @param Request $request
     * @param $role
     * @return JsonResponse$role
     */
    public function edit(Request $request, $role)
    {
        return response()->json($this->editRole($request, $role),200);

    }


    /**
     * @param Request $request
     * @param $role
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $role)
    {
        $request->merge(['institutionTypes' => ['independant']]);

        $this->validate($request, $this->rule($role));

        $role = Role::whereName($role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->firstOrFail();

        $this->checkIsEditableRole($role);

        $this->verifiedStore($request);

        return response()->json($this->updateRole($request, $role), 201);

    }


    /**
     * @param $role
     * @return JsonResponse
     */
    public function destroy($role)
    {

            $role = Role::whereName($role)->where('guard_name', 'api')->firstOrFail();
            $this->checkIsEditableRole($role);
            $this->checkIsUsedRole($role);
            $role->delete();
            return response()->json($role,200);

            /*$role = Role::whereName($role)->where('guard_name', 'api')->firstOrFail();
            $this->checkIsEditableRole($role);
            $role->delete();
            return response()->json($role,200);*/

    }

}
