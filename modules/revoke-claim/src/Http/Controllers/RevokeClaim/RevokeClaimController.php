<?php

namespace Satis2020\RevokeClaim\Http\Controllers\RevokeClaim;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Notifications\RevokeClaimClaimerNotification;
use Satis2020\ServicePackage\Notifications\RevokeClaimStaffNotification;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Symfony\Component\HttpFoundation\Response;

class RevokeClaimController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\Notification;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:revoke-claim')->only(['update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(Request $request, Claim $claim)
    {

        $request->merge(['claimStatus' => $claim->status]);

        $rules = [
            'claimStatus' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, ['incomplete', 'full'])) {
                        $fail("La réclamation est déjà en cours de traitement");
                    }
                }
            ],
        ];

        $this->validate($request, $rules);

        // notify the claimer
        $claim->claimer->notify(new RevokeClaimClaimerNotification($claim));

        // notify the pilot
        $pilotIdentity = $this->getInstitutionPilot(is_null($claim->createdBy) ? $claim->institutionTargeted :
            $claim->createdBy->institution);
        if (!is_null($pilotIdentity))
            $pilotIdentity->notify(new RevokeClaimStaffNotification($claim));

        $claim->update(['revoked_at' => Carbon::now(), 'revoked_by' => $this->staff()->id]);

        $claim->refresh();

        $this->activityLogService->store("Reclamation revoquée.",
            $this->institution()->id,
            $this->activityLogService::CLAIM_REVOKED,
            'claim',
            $this->user(),
            $claim
        );

        return response()->json($claim, Response::HTTP_OK);
    }

}
