<?php

namespace Satis2020\StaffFromMaybeNoUnit\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Staff;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Class ImportExportController
 * @package Satis2020\StaffFromMaybeNoUnit\Http\Controllers\ImportExport
 */

class ImportController extends ApiController
{
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-staff-from-maybe-no-unit')->only(['importStaffs']);

        $this->activityLogService = $activityLogService;
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

        $unitRequired = false;

        $stop_identite_exist = $request->stop_identite_exist;

        $etat = $request->etat;

        $imports = new Staff($etat, $unitRequired, $myInstitution, $stop_identite_exist);

        $imports->import($file);

        if($imports->getErrors()){
            $datas = [

                'status' => false,
                'staffs' => $imports->getErrors()
            ];
        }else{
            $this->activityLogService->store("Importation des staffs",
                $this->institution()->id,
                ActivityLogService::IMPORTATION,
                'staff',
                $this->user()
            );
        }

        return response()->json($datas,201);
    }


}

