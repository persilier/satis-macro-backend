<?php

namespace Satis2020\StaffFromMyUnit\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Client\TransactionClientImport;
use Satis2020\ServicePackage\Imports\Staff;
use Satis2020\ServicePackage\Imports\Staff\TransactionStaffImport;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Requests\Imports\ImportStaffRequest;

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
        $this->middleware('permission:store-staff-from-my-unit')->only(['importStaffs']);
    }

    /**
     * @param ImportStaffRequest $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function importStaffs(ImportStaffRequest $request){

        $institution = $this->institution();


        $datas = [
            'status' => true,
            'staffs' => ''
        ];

        $institutions = Institution::query()->get(['id', 'name']);

        $myInstitution = $institution;

        $unitRequired = true;

        $stop_identite_exist = $request->stop_identite_exist;

        $etat = $request->etat_update;

        $data = compact("etat","unitRequired","myInstitution","stop_identite_exist","institutions");
        $transaction =  new TransactionStaffImport(
            $myInstitution,
            $data
        );

        Excel::import($transaction, $request->file('file'));

        $datas['errors'] = $transaction->getImportErrors();

        return response()->json($datas,201);
    }


}

