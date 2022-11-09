<?php

namespace Satis2020\StaffFromAnyUnit\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Staff;
use Satis2020\ServicePackage\Imports\Staff\TransactionStaffImport;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class ImportExportController
 * @package Satis2020\StaffFromAnyUnit\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
       // $this->middleware('permission:store-staff-from-any-unit')->only(['importStaffs']);
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
        $unitRequired = true;
        $stop_identite_exist = $request->stop_identite_exist;
        $etat = $request->etat_update;
        $institutions = Institution::query()->get(['id', 'name']);


        $data = compact("etat","unitRequired","myInstitution","stop_identite_exist","institutions");
        $transaction =  new TransactionStaffImport(
            $myInstitution,
            $data
        );

        Excel::import($transaction, $request->file('file'));

        $this->activityLogService->store("Importation des staffs",
            $this->institution()->id,
            ActivityLogService::IMPORTATION,
            'staff',
            $this->user()
        );

        $datas['errors'] = $transaction->getImportErrors();

        $this->activityLogService->store("Importation des staffs",
            $this->institution()->id,
            ActivityLogService::IMPORTATION,
            'staff',
            $this->user()
        );

        return response()->json($datas,201);
    }


}

