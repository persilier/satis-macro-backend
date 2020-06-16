<?php

namespace Satis2020\UpdateClaimWithoutClient\Http\Controllers\Claims;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\UpdateClaim;
class ClaimController extends ApiController
{
    use  CreateClaim, UpdateClaim;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-incomplete-without-client')->only(['index']);
        $this->middleware('permission:show-claim-incomplete-without-client')->only(['show']);
        $this->middleware('permission:update-claim-incomplete-without-client')->only(['update']);
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return response()->json(
            $this->getAllClaimCompleteOrIncomplete($this->institution()->id,'incomplete'),
        200);
    }


    public function show($claimId)
    {
        return response()->json(
            $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claimId ,'incomplete'),
        200);
    }

    /**
     * Display the specified resource.
     *
     * @param claimId $claimId
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($claimId)
    {
        $claim = $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claimId ,'incomplete');
        $datas = $this->getDataEditWithoutClient($claim);
        return response()->json($datas,200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  $claimId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $claimId)
    {
        $this->validate($request, $this->rules($request , false, true, false));

        $claim = $this->getClaimUpdate($this->institution()->id, $claimId, 'incomplete');

        // Check if the claim is complete
        $request->merge(['status' => $this->getStatus($request, false, true, false)]);

        $claim = $this->updateClaim($request, $claim, $this->staff()->id);

        return response()->json($claim,201);
    }

}
