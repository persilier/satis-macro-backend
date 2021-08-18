<?php

namespace Satis2020\WithoutLinkWithInstitutionUnit\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\UniteTypeUnite;

/**
 * Class ImportExportController
 * @package Satis2020\WithoutLinkWithInstitutionUnit\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-without-link-unit')->only(['importUnitTypeUnit', 'downloadFile']);
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
            'unitTypeUnit' => '',
        ];

        $file = $request->file('file');

        $imports = new UniteTypeUnite(null, true);

        $imports->import($file);

        if ($imports->getErrors()) {
            $datas = [
                'status' => false,
                'units' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }


}

