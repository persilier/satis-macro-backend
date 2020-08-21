<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAssignmentToStaffs;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Notifications\TreatAClaim;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Traits\Notification;

/**
 * Class ClaimAssignmentToStaffController
 * @package Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAssignmentToStaffs
 */
class ClaimAssignmentToStaffController extends ApiController
{
    use ClaimAwaitingTreatment;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-assignment-to-staff')->only(['index']);
        $this->middleware('permission:show-claim-assignment-to-staff')->only(['show', 'treatmentClaim', 'unfoundedClaim']);
    }


    /**
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        $institution = $this->institution();
        $staff = $this->staff();

        $claims = $this->getClaimsTreat($institution->id, $staff->unit_id, $staff->id)->get()->map(function ($item, $key) {
            $item = Claim::with($this->getRelationsAwitingTreatment())->find($item->id);
            return $item;
        });
        return response()->json($claims, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function show($claim)
    {
        $institution = $this->institution();
        $staff = $this->staff();

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);
        return response()->json($claim, 200);
    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    protected function treatmentClaim(Request $request, $claim)
    {

        $institution = $this->institution();

        $staff = $this->staff();

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);

        $rules = [
            'amount_returned' => [
                'nullable',
                'integer',
                Rule::requiredIf(!is_null($claim->amount_disputed) && !is_null($claim->amount_currency_slug)),
                'min:0'
            ],
            'solution' => ['required', 'string'],
            'comments' => ['nullable', 'string'],
            'preventive_measures' => ['nullable', 'string']
        ];

        $this->validate($request, $rules);

        $claim->activeTreatment->update([
            'amount_returned' => $request->amount_returned,
            'solution' => $request->solution,
            'comments' => $request->comments,
            'preventive_measures' => $request->preventive_measures,
            'solved_at' => Carbon::now()
        ]);

        $claim->update(['status' => 'treated']);

        if(!is_null($this->getInstitutionPilot($institution))){
            $this->getInstitutionPilot($institution)->notify(new TreatAClaim($claim));
        }

        return response()->json($claim, 200);

    }


    /**
     * @param Request $request
     * @param $claim
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    protected function unfoundedClaim(Request $request, $claim)
    {

        $institution = $this->institution();
        $staff = $this->staff();

        $this->validate($request, $this->rules($staff, 'unfounded'));

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id, $claim);

        $claim->activeTreatment->update(['unfounded_reason' => $request->unfounded_reason, 'declared_unfounded_at' => Carbon::now()]);

        $claim->update(['status' => 'treated']);

        if(!is_null($this->getInstitutionPilot($institution))){
            $this->getInstitutionPilot($institution)->notify(new TreatAClaim($claim));
        }

        return response()->json($claim, 200);

    }


}
