<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\ImportExport;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Request;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Client\TransactionClientImport;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
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
        $datas = [
            'status' => true,
            'clients' => ''
        ];

        $staff = Staff::query()
            ->where('identite_id', Auth::user()->identite_id)
            ->first(['institution_id']);

        $myInstitution = Institution::query()
            ->where('id', $staff->institution_id)
            ->first(['id']);

        $institutions = Institution::query()->get(['id', 'name']);
        $categoryClients = CategoryClient::query()->get(['id', 'name']);
        $accountTypes = AccountType::query()->get(['id', 'name']);

        $data = compact('institutions', 'categoryClients', 'accountTypes');

        $transaction = new TransactionClientImport(
            $myInstitution,
            $data
        );

        Excel::import(
            $transaction,
            $request->file('file')
        );

        $datas['errors'] = $transaction->getImportErrors();


        return response()->json($datas, 201);
    }


}

