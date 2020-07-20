<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Client;

/**
 * Class ImportExportController
 * @package Satis2020\ClientFromAnyInstitution\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-client-from-any-institution')->only(['importClient']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importClients(Request $request){

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
            'etat_update' => 'required|boolean'
        ]);

        $datas = [
            'status' => false,
            'clients' => ''
        ];

        $file = $request->file('file')->store('import');

        $etat = $request->etat_update;

        $myInstitution = false;

        $imports = new Client($etat, $myInstitution);

        $imports->import($file);

        if($imports->getErrors()){
            $datas = [

                'status' => true,
                'clients' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }


}

