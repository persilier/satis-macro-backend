<?php

namespace Satis2020\AttachFilesToClaim\Http\Controllers\AttachFiles;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Class AttachFilesController
 * @package Satis2020\AttachFilesToClaim\Http\Controllers\AttachFiles
 */
class AttachFilesController extends ApiController
{
    use CreateClaim, ActivePilot;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:attach-files-to-claim')->only(['index']);
        $this->activityLogService = $activityLogService;
    }


    /**
     * @param Request $request
     * @param $claim_id
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request, $claim_id)
    {

        $this->validate($request, [
            'file' => 'required',
            'file.*' => 'required|max:20000|mimes:doc,pdf,docx,txt,jpeg,bmp,png,xls,xlsx,csv'
        ]);

        $staff = $this->staff();
        $condition2 = !$this->allowOnlyActivePilot($staff);
        if (!$claim = Claim::where(function ($query) use ($staff) {
            $query->where('created_by', $staff->id)->orWhereHas('activeTreatment', function ($q) use ($staff) {
                $q->where('responsible_staff_id', $staff->id);
            });
        })->where('status', '!=', 'archived')->find($claim_id) and $condition2) {

            return response()->json('Vous n\'êtes pas autorisé à joindre des fichiers à cette réclamation.', 404);
        }
        if (!$claim) {
            $claim = Claim::where('status', '!=', Claim::CLAIM_ARCHIVED)->find($claim_id);
        }
        $this->uploadAttachments($request, $claim);

        $this->activityLogService->store(
            "Ajout de fichier(s) supplémentaire(s) au réclamation",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim->files, 201);
    }
}
