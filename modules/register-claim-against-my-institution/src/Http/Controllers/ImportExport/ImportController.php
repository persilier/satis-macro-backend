<?php

namespace Satis2020\RegisterClaimAgainstMyInstitution\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Claim;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Class ImportController
 * @package Satis2020\RegisterClaimAgainstAnyInstitution\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
       // $this->middleware('permission:store-claim-against-my-institution')->only(['importClaims']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function importClaims(Request $request){

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
            'etat_update' => 'required|boolean',
        ]);

        $data = [
            'status' => true,
            'claims' => ''
        ];

        $file = $request->file('file');

        $institution = $this->institution();

        $etat = $request->etat_update;

        $myInstitution = $institution->acronyme;

        $imports = new Claim($etat, $myInstitution, true, false, true);

        $imports->import($file);

        if($imports->getErrors()){

            $data = [
                'status' => false,
                'claims' => $imports->getErrors()
            ];
        }else{
            $this->activityLogService->store("Reclamations importées.",
                $this->institution()->id,
                $this->activityLogService::IMPORTATION,
                'claim',
                $this->user()
            );
        }

        return response()->json($data,201);

    }


}

