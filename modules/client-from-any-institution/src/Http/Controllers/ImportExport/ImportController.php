<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $this->middleware('permission:store-client-from-any-institution')->only(['importClient', 'downloadFile']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importClients(Request $request){

        $request->validate([

            'file' => 'required|file|max:2048|mimes:xls,xlsx',
            'etat_update' => 'required|boolean',
            'stop_identite_exist' => 'required|boolean'
        ]);

        $datas = [

            'status' => true,
            'clients' => ''
        ];

        $file = $request->file('file');

        $etat = $request->etat_update;

        $stop_identite_exist = $request->stop_identite_exist;

        $myInstitution = false;

        $imports = new Client($etat, $myInstitution, $stop_identite_exist);

        $imports->import($file);

        if($imports->getErrors()){

            $datas = [

                'status' => false,
                'clients' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }


    /**
     * @return BinaryFileResponse
     */
    public function downloadFile(){

        $filename = "FORMAT_EXCEL_CLIENT.xlsx";
        // Get path from storage directory
        $path = storage_path('app/excels/' . $filename);

        // Download file with custom headers
        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }


}

