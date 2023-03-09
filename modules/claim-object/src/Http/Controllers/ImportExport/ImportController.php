<?php

namespace Satis2020\ClaimObject\Http\Controllers\ImportExport;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\ClaimObject;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * Class ImportController
 * @package Satis2020\ClaimObject\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    use \Satis2020\ServicePackage\Traits\ClaimObject;
    protected $activityLogService;
    public function __construct(ActivityLogService $activityLogService)

    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-claim-object')->only(['importClaimObjects', 'downloadFile']);
        $this->activityLogService =  $activityLogService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function importClaimObjects(Request $request)
    {

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
        ]);

        $datas = [
            'status' => true,
            'claimObjects' => '',

        ];

        $file = $request->file('file');

        $institution = $this->institution();

        $myInstitution = $institution->name;

        $imports = new ClaimObject($myInstitution);

        $imports->import($file);

        $datas['errors'] =  $imports->getErrors();

        $this->activityLogService->store("Importation d'une liste de catégories et d'objets de réclamation",
            $this->institution()->id,
            $this->activityLogService::CREATED,
            'claim_object',
            $this->user()
        );

        return response()->json($datas,201);
    }

}

