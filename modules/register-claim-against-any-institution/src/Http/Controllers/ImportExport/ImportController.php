<?php

namespace Satis2020\RegisterClaimAgainstAnyInstitution\Http\Controllers\ImportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Imports\Claim;
/**
 * Class ImportController
 * @package Satis2020\RegisterClaimAgainstAnyInstitution\Http\Controllers\ImportExport
 */
class ImportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-claim-against-any-institution')->only(['importClaims']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importClaims(Request $request){

        $request->validate([
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
            'etat_update' => 'required|boolean',
        ]);

        $datas = [

            'status' => true,
            'claims' => ''
        ];

        $file = $request->file('file');

        $etat = $request->etat_update;

        $myInstitution = false;

        $imports = new Claim($etat, $myInstitution, true, false, true);

        $imports->import($file);

        if($imports->getErrors()){

            $datas = [

                'status' => false,
                'claims' => $imports->getErrors()
            ];
        }

        return response()->json($datas,201);

    }


}

