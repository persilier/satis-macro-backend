<?php

namespace Satis2020\ClaimObject\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\ClaimObject;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * Class ImportController
 * @package Satis2020\ClaimObject\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    use \Satis2020\ServicePackage\Traits\ClaimObject;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-claim-object')->only(['importClaimObjects', 'downloadFile']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importClaimObjects(Request $request){

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

        if($imports->getErrors()){

            $datas = [

                'status' => false,
                'claimObjects' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }

}

