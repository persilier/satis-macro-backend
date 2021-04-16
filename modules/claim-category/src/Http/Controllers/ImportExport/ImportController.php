<?php

namespace Satis2020\ClaimCategory\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\ClaimCategory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


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
        $this->middleware('permission:store-claim-category')->only(['importClaimCategories', 'downloadFile']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importClaimCategories(Request $request){

        $request->validate([

            'file' => 'required|file|max:2048|mimes:xls,xlsx',
        ]);

        $datas = [

            'status' => true,
            'claimCategories' => '',
        ];

        $file = $request->file('file');

        $imports = new ClaimCategory();

        $imports->import($file);

        if($imports->getErrors()){

            $datas = [

                'status' => false,
                'claimCategories' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }

}

