<?php

namespace Satis2020\AnyInstitutionUnit\Http\Controllers\ImportExport;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\UniteTypeUnite;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Class ImportExportController
 * @package Satis2020\AnyInstitutionUnit\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-any-unit')->only(['importUnitTypeUnit', 'downloadFile']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function importUnitTypeUnit(Request $request){

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
        ]);

        $datas = [
            'status' => true,
            'unitTypeUnit' => '',
        ];

        $file = $request->file('file');

        $imports = new UniteTypeUnite(false);

        $imports->import($file);

        if ($imports->getErrors()) {
            $datas = [
                'status' => false,
                'units' => $imports->getErrors()
            ];
        }

        $this->activityLogService->store("Importation des unités et types d'unités par excel",
            $this->institution()->id,
            $this->activityLogService::IMPORTATION,
            'unit',
            $this->user()
        );

        return response()->json($datas,201);

    }


}

