<?php
namespace Satis2020\AnyUser\Http\Controllers\IdentiteRole;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\UserTrait;

/**
 * Class IdentiteRoleController
 * @package Satis2020\AnyUser\Http\Controllers\IdentiteRole
 */
class IdentiteRoleController extends ApiController
{
    use UserTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
    }


    /**
     * @param Institution $institution
     * @return JsonResponse
     */
    public function index(Institution $institution)
    {
        $data = $this->getAllIdentitesRoles($institution);

        return response()->json($data,200);

    }

}
