<?php

namespace Satis2020\StaffFromAnyUnit\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\RestoreStaffRole;
use Satis2020\ServicePackage\Imports\Staff;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class ImportExportController
 * @package Satis2020\StaffFromAnyUnit\Http\Controllers\ImportExport
 */
class ImportAndRestoreController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
       // $this->middleware('permission:store-staff-from-any-unit')->only(['importStaffs']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request){

        $datas = [
            'status' => true,
        ];
        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx'
        ]);

        $file = $request->file('file');


        $imports = new RestoreStaffRole();
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

