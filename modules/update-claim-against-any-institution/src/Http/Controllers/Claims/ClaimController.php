<?php

namespace Satis2020\UpdateClaimAgainstAnyInstitution\Http\Controllers\Claims;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\UpdateClaim;

/**
 * Class ClaimController
 * @package Satis2020\UpdateClaimAgainstAnyInstitution\Http\Controllers\Claims
 */
class ClaimController extends ApiController
{
    use  CreateClaim, UpdateClaim;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-incomplete-against-any-institution')->only(['index']);
        $this->middleware('permission:show-claim-incomplete-against-any-institution')->only(['show']);
        $this->middleware('permission:update-claim-incomplete-against-any-institution')->only(['update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        return response()->json(
            $this->getAllClaimCompleteOrIncomplete($this->institution()->id,'incomplete'),
        200);
    }


    /**
     * @param $claimId
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function show($claimId)
    {
        return response()->json(
            $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claimId ,'incomplete'),
        200);
    }

    /**
     * Display the specified resource.
     *
     * @param $claimId
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function edit($claimId)
    {

        $claim = $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claimId ,'incomplete');
        $claims = $this->getDataEdit($claim);
        return response()->json($claims,200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  $claimId
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function update(Request $request, $claimId)
    {

        $this->validate($request, $this->rules($request, true, false , true , true ));

        $claim = $this->getClaimUpdate($this->institution()->id, $claimId, 'incomplete');

        $request->merge(['status' => $this->getStatus($request, true, false, true, true)]);

        $claim = $this->updateClaim($request, $claim, $this->staff()->id);

        $this->uploadAttachments($request, $claim);

        return response()->json($claim,201);
    }

}
