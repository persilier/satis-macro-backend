<?php

namespace Satis2020\MyInstitutionTypeRole\Http\Controllers\ImportExport;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Imports\AddProfilToRole;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Models\Module;
use Satis2020\ServicePackage\Traits\RoleTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Class AddRolesToProfilsController
 * @package Satis2020\AnyInstitutionTypeRole\Http\Controllers\Role
 */
class AddRolesToProfilsController extends ApiController
{
    use RoleTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-my-institution-type-role')->only(['store']);
    }


    public function store(Request $request)
    {

        $request->validate(['file' => 'required|file|max:2048|mimes:xls,xlsx']);
        $datas = [
            'status' => true,
            'data' => ''
        ];
        $file = $request->file('file');

        $imports = new AddProfilToRole();

        $imports->import($file);

        if($imports->getErrors()){
            $datas = [
                'status' => false,
                'staffs' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);
    }


}
