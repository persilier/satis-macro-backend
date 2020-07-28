<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Staff;

/**
 * Class ImportExportController
 * @package Satis2020\StaffFromMyUnit\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-staff-from-my-unit')->only(['importClient']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importStaffs(Request $request){

        $institution = $this->institution();

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
            'etat_update' => 'required|boolean',
            'stop_identite_exist' => 'required|boolean'
        ]);

        $datas = [
            'status' => true,
            'staffs' => ''
        ];

        $file = $request->file('file')->store('import');

        $myInstitution = $institution->name;

        $unitRequired = true;

        $stop_identite_exist = $request->stop_identite_exist;

        $etat = $request->etat;

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


}

