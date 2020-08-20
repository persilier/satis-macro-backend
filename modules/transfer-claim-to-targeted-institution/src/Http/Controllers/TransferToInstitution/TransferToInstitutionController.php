<?php

namespace Satis2020\TransferClaimToTargetedInstitution\Http\Controllers\TransferToInstitution;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;
use Satis2020\ServicePackage\Notifications\TransferredToTargetedInstitution;
use Satis2020\ServicePackage\Traits\AwaitingAssignment;
use Satis2020\ServicePackage\Traits\Notification;

class TransferToInstitutionController extends ApiController
{

    use AwaitingAssignment, Notification;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:transfer-claim-to-targeted-institution')->only(['update']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, Claim $claim)
    {
        $claim->load('activeTreatment');
        $activeTreatment = $claim->activeTreatment;
        if (is_null($activeTreatment)) {
            $activeTreatment = Treatment::create(['claim_id' => $claim->id]);
        }
        $activeTreatment->update(['transferred_to_targeted_institution_at' => Carbon::now()]);
        $claim->update(['status' => 'transferred_to_targeted_institution']);

        // send notification to pilot
        try {
            $this->getInstitutionPilot(Institution::find($claim->institution_targeted_id))->notify(new TransferredToTargetedInstitution($claim));
        } catch (\Exception $exception) {}

        return response()->json($claim, 201);
    }

}
