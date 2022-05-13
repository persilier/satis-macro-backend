<?php

namespace Satis2020\MyInstitutionUnit\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\UniteTypeUnite;

/**
 * Class ImportExportController
 * @package Satis2020\MyInstitutionUnit\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-my-unit')->only(['importUnitTypeUnit', 'downloadFile']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importUnitTypeUnit(Request $request){

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
        ]);

        $institution = $this->institution();

        $datas = [
            'status' => true,
        ];

        $file = $request->file('file');

        $myInstitution = $institution->name;

        $imports = new UniteTypeUnite($myInstitution);

        $imports->import($file);

        if ($imports->getErrors()) {
            $datas = [
                'errors' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }


}

