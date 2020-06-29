<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAssignmentToStaffs;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;

class ClaimAssignmentToStaffController extends ApiController
{
    use ClaimAwaitingTreatment;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-assignment-to-staff')->only(['index']);
        $this->middleware('permission:show-claim-assignment-to-staff')->only(['show', 'treatmentClaim','treatmentClaim']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
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
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function show($claim)
    {
        $institution = $this->institution();
        $staff = $this->staff();

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id,  $claim);
        return response()->json($claim, 200);
    }


    protected function treatmentClaim(Request $request, $claim){

        $institution = $this->institution();
        $staff = $this->staff();

        $this->validate($request, $this->rules($staff, 'treatment'));

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id,  $claim);

        $claim->activeTreatment->update([
            'amount_returned'   => $request->amount_returned,
            'solution'          => $request->solution,
            'comments'          => $request->comments,
            'preventive_measures' => $request->preventive_measures,
            'solved_at'         => Carbon::now()
        ]);

        $claim->update([ 'status' => 'treated']);

        return response()->json($claim, 200);

    }


    protected function unfoundedClaim(Request $request, $claim){

        $institution = $this->institution();
        $staff = $this->staff();

        $this->validate($request, $this->rules($staff, 'unfounded'));

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id,  $claim);

        $claim->activeTreatment->update(['unfounded_reason' => $request->unfounded_reason, 'declared_unfounded_at' => Carbon::now()]);

        $claim->update([ 'status' => 'treated']);

        return response()->json($claim, 200);

    }


}
