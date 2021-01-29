<?php

namespace Satis2020\AttachFilesToClaim\Http\Controllers\AttachFiles;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\CreateClaim;

/**
 * Class AttachFilesController
 * @package Satis2020\AttachFilesToClaim\Http\Controllers\AttachFiles
 */
class AttachFilesController extends ApiController
{
    use CreateClaim;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:attach-files-to-claim')->only(['index']);
    }


    /**
     * @param Request $request
     * @param $claim_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request, $claim_id)
    {

        $this->validate($request,[
            'file' => 'required',
            'file.*' => 'required|mimes:doc,pdf,docx,txt,jpeg,bmp,png,xls,xlsx,csv'
        ]);

        $staff = $this->staff();

        if(!$claim = Claim::where(function ($query) use ($staff){

            $query->where('created_by', $staff->id)->orWhereHas('activeTreatment', function($q) use ($staff){
                $q->where('responsible_staff_id', $staff->id);
            });

        })->where('status', '!=', 'archived')->find($claim_id)){

            return response()->json('Vous n\'êtes pas autorisé à joindre des fichiers à cette réclamation.',404);
        }

        $this->uploadAttachments($request, $claim);

        return response()->json($claim->files,201);

    }

}
