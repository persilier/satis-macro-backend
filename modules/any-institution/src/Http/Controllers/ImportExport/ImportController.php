<?php

namespace Satis2020\AnyInstitution\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Institution;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;


/**
 * Class ImportController
 * @package Satis2020\ClaimObject\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-any-institution')->only(['importInstitutions']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importInstitutions(Request $request){

        $request->validate([

            'file' => 'required|file|max:2048|mimes:xls,xlsx',
        ]);

        $datas = [

            'status' => true,
            'institutions' => '',
        ];

        $file = $request->file('file');

        $imports = new Institution();

        $imports->import($file);

        if($imports->getErrors()){

            $datas = [
                'status' => false,
                'institutions' => $imports->getErrors()
            ];
        }

        $this->activityLogService->store("Importation des institutions par excel",
            $this->institution()->id,
            $this->activityLogService::IMPORTATION,
            'institution',
            $this->user()
        );

        return response()->json($datas,201);

    }

}

