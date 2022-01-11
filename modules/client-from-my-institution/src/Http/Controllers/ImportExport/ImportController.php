<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\ImportExport;

use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Client\TransactionClientImport;
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
        $myInstitution = $this->institution();

        Excel::import(
            new TransactionClientImport(
                $myInstitution,
                $request->etat_update,
                $request->stop_identite_exist
            ),
            $request->file('file'),
            'public',
            \Maatwebsite\Excel\Excel::CSV
        );

        return response()->json(['status' => true, 'clients' => ''], 201);
    }


}

