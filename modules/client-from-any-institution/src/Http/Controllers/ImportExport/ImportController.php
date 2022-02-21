<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\ImportExport;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Client;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class ImportExportController
 * @package Satis2020\ClientFromAnyInstitution\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-client-from-any-institution')->only(['importClient', 'downloadFile']);

        $this->activityLogService = $activityLogService;
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

        $this->activityLogService->store('Importation des comptes clients par fichier excel',
            $this->institution()->id,
            $this->activityLogService::IMPORTATION,
            'account',
            $this->user()
        );

        return response()->json($datas,201);

    }


}

