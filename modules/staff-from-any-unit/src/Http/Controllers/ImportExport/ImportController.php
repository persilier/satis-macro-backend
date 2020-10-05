<?php

namespace Satis2020\StaffFromAnyUnit\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Staff;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class ImportExportController
 * @package Satis2020\StaffFromAnyUnit\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-staff-from-any-unit')->only(['importStaffs']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importStaffs(Request $request){

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
            'etat_update' => 'required|boolean',
            'stop_identite_exist' => 'required|boolean'
        ]);

        $datas = [
            'status' => true,
            'staffs' => ''
        ];

        $file = $request->file('file');

        $myInstitution = false;

        $unitRequired = true;

        $stop_identite_exist = $request->stop_identite_exist;

        $etat = $request->etat_update;

        $imports = new Staff($etat, $unitRequired, $myInstitution, $stop_identite_exist);

        $imports->import($file);

        if($imports->getErrors()){

            $datas = [

                'status' => false,
                'staffs' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }


    /**
     * @return BinaryFileResponse
     */
    public function downloadFile(){

        return response()->download(public_path('excels/FORMAT_EXCEL_CLIENT.xlsx'));
    }


}

