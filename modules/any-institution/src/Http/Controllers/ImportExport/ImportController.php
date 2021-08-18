<?php

namespace Satis2020\AnyInstitution\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Institution;


/**
 * Class ImportController
 * @package Satis2020\ClaimObject\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-any-institution')->only(['importInstitutions']);
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

        return response()->json($datas,201);

    }

}

