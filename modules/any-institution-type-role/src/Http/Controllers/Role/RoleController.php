<?php

namespace Satis2020\AnyInstitutionTypeRole\Http\Controllers\Role;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\RoleTrait;
use Spatie\Permission\Models\Role;

/**
 * Class RoleController
 * @package Satis2020\AnyInstitutionTypeRole\Http\Controllers\Role
 */
class RoleController extends ApiController
{
    use RoleTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-any-institution-type-role')->only(['index']);
        $this->middleware('permission:show-any-institution-type-role')->only(['show']);
        $this->middleware('permission:store-any-institution-type-role')->only(['create', 'store']);
        $this->middleware('permission:update-any-institution-type-role')->only(['edit', 'update']);
        $this->middleware('permission:destroy-any-institution-type-role')->only(['destroy']);

        $this->activityLogService = $activityLogService;

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

        return response()->json($this->getAllDatecreateRole($request),200);

    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rule());

        $this->verifiedStore($request);

        $role = $this->createRole($request);

        $this->activityLogService->store("Enregistrement d'un rôle.",
            $this->institution()->id,
            $this->activityLogService::CREATED,
            'role',
            $this->user(),
            $role
        );

        return response()->json($role, 201);

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
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(Request $request, $role)
    {
        $this->validate($request, $this->rule($role));

        $role = Role::whereName($role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->firstOrFail();

        $this->checkIsEditableRole($role);

        $this->verifiedStore($request);

        $role = $this->updateRole($request, $role);

        $this->activityLogService->store("Modification d'un rôle.",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'role',
            $this->user(),
            $role
        );

        return response()->json($role, 201);

    }


    /**
     * @param $role
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function destroy($role)
    {

        $role = Role::whereName($role)->where('guard_name', 'api')->firstOrFail();
        $this->checkIsEditableRole($role);
        $role->delete();

        $this->activityLogService->store("Suppression d'un rôle.",
            $this->institution()->id,
            $this->activityLogService::DELETED,
            'role',
            $this->user(),
            $role
        );

        return response()->json($role,200);

    }
}
