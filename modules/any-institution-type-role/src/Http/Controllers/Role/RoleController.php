<?php

namespace Satis2020\AnyInstitutionTypeRole\Http\Controllers\Role;

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
        $this->middleware('permission:list-any-institution-type-role')->only(['index']);
        $this->middleware('permission:show-any-institution-type-role')->only(['show']);
        $this->middleware('permission:store-any-institution-type-role')->only(['create', 'store']);
        $this->middleware('permission:update-any-institution-type-role')->only(['edit', 'update']);
        $this->middleware('permission:destroy-any-institution-type-role')->only(['destroy']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Role::all(),200);
    }


    /**
     * @return JsonResponse
     */
    public function create(){

        return response()->json(InstitutionType::all(),200);

    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rule());

        $this->verifiedStore($request);

        return response()->json($this->createRole($request), 200);

    }


    /**
     * @param $role
     * @return JsonResponse
     */
    public function show($role)
    {

        return response()->json(Role::whereName($role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->with('permissions')->firstOrFail(),200);

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
        $this->validate($request, $this->rule($role));

        $role = Role::whereName($role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->firstOrFail();

        $this->verifiedStore($request);

        return response()->json($this->updateRole($request, $role), 200);

    }


    /**
     * @param $role
     * @return JsonResponse
     */
    public function destroy($role)
    {

        $role = Role::whereName($role)->where('guard_name', 'api')->firstOrFail();
        $role->delete();

        return response()->json($role,200);

    }
}
