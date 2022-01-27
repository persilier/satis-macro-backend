<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\ImportExport;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Client\TransactionClientImport;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Requests\Imports\ImportClientRequest;

/**
 * Class ImportExportController
 * @package Satis2020\ClientFromMyInstitution\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-client-from-my-institution')
            ->only(['importClient', 'downloadFile']);
    }

    /**
     * @param ImportClientRequest $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function importClients(ImportClientRequest $request)
    {

        $staff = Staff::query()
            ->where('identite_id', Auth::user()->identite_id)
            ->first(['id']);

        $myInstitution = Institution::query()
            ->where('id', $staff->institution_id)
            ->first(['id']);

        Excel::import(
            new TransactionClientImport(
                $myInstitution,
                $request->etat_update,
                $request->stop_identite_exist
            ),
            $request->file('file')
        );

        return response()->json(['status' => true, 'clients' => ''], 201);
    }


}

