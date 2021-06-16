<?php

namespace Satis2020\AnyInstitutionTypeRole\Http\Controllers\Permission;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Traits\RoleTrait;

/**
 * Class RoleController
 * @package Satis2020\AnyInstitutionTypeRole\Http\Controllers\InstitutionType
 */
class PermissionController extends ApiController
{

    use RoleTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, ['institutionTypes.*' => 'required']);
        return response()->json($this->getAllPermissions($request),200);

    }

}
