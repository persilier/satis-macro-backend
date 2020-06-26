<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAwaitingTreatments;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;

class ClaimAwaitingTreatmentController extends ApiController
{
    use ClaimAwaitingTreatment;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-awaiting-treatment')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-treatment')->only(['show']);
        $this->middleware('permission:rejected-claim-awaiting-treatment')->only(['show', 'rejectedClaim']);
        $this->middleware('permission:self-assignment-claim-awaiting-treatment')->only(['show', 'selfAssignment']);
        $this->middleware('permission:assignment-claim-awaiting-treatment')->only(['edit', 'assignmentClaimStaff']);
        $this->middleware('permission:unfounded-claim-awaiting-treatment')->only(['showClaimQueryTreat','unfoundedClaim']);
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

        $claims = $this->getClaimsQuery($institution->id, $staff->unit_id)->get()->map(function ($item, $key) {
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

        $claim = $this->getOneClaimQuery($institution->id, $staff->unit_id, $claim);
        return response()->json(Claim::with($this->getRelationsAwitingTreatment())->findOrFail($claim->id), 200);
    }


    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function edit($claim)
    {
        $institution = $this->institution();
        $staff = $this->staff();

        $claim = $this->getOneClaimQuery($institution->id, $staff->unit_id, $claim);
        return response()->json(['claim' => $claim, 'staffs' => Staff::with('identite')->where('unit_id',$staff->unit_id)->get()
            ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function showClaimQueryTreat($claim)
    {
        $institution = $this->institution();
        $staff = $this->staff();

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id,  $claim);
        return response()->json($claim, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function rejectedClaim(Request $request, $claim)
    {
        $institution = $this->institution();
        $staff = $this->staff();

        $this->validate($request, $this->rules($staff, 'rejected'));

        $claim = $this->getOneClaimQuery($institution->id, $staff->unit_id, $claim);

        $claim = $this->rejectedClaimUpdate($claim, $request);

        return response()->json($claim, 200);

    }


    protected  function selfAssignmentClaim($claim){

        $institution = $this->institution();
        $staff = $this->staff();

        $claim = $this->getOneClaimQuery($institution->id, $staff->unit_id, $claim);

        $claim = $this->assignmentClaim($claim, $staff->id);

        return response()->json($claim, 200);
    }


    protected  function assignmentClaimStaff(Request $request, $claim){

        $institution = $this->institution();
        $staff = $this->staff();

        if(!$this->checkLead($staff)){
            throw new CustomException("Impossible d'affecter cette réclamation à un staff.");
        }

        $this->validate($request, $this->rules($staff));


        $claim = $this->getOneClaimQuery($institution->id, $staff->unit_id, $claim);

        $claim = $this->assignmentClaim($claim, $request->staff_id);

        return response()->json($claim, 200);
    }


    protected function unfoundedClaim(Request $request, $claim){

        $institution = $this->institution();
        $staff = $this->staff();

        $this->validate($request, $this->rules($staff, 'unfounded'));

        $claim = $this->getOneClaimQueryTreat($institution->id, $staff->unit_id, $staff->id,  $claim);

        $claim->activeTreatment->update(['unfounded_reason' => $request->unfounded_reason, 'declared_unfounded_at' => Carbon::now()]);

        $claim->update([ 'status' => 'status']);

        return response()->json($claim, 200);

    }

}
