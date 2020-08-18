<?php

namespace Satis2020\UpdateClaimAgainstMyInstitution\Http\Controllers\Claims;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\UpdateClaim;
use Illuminate\Support\Arr;

/**
 * Class ClaimController
 * @package Satis2020\UpdateClaimAgainstMyInstitution\Http\Controllers\Claims
 */
class ClaimController extends ApiController
{
    use  CreateClaim, UpdateClaim;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-incomplete-against-my-institution')->only(['index']);
        $this->middleware('permission:show-claim-incomplete-against-my-institution')->only(['show']);
        $this->middleware('permission:update-claim-incomplete-against-my-institution')->only(['update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(
            $this->getAllClaimCompleteOrIncompleteForMyInstitution($this->institution()->id,'incomplete'),
        200);
    }


    /**
     * @param $claimId
     * @return JsonResponse
     */
    public function show($claimId)
    {
        return response()->json(
            $this->getOneClaimCompleteOrIncompleteForMyInstitution($this->institution()->id, $claimId ,'incomplete'),
        200);
    }

    /**
     * Display the specified resource.
     * @param $claimId
     * @\Illuminate\Http\Response
     * @return JsonResponse
     */
    public function edit($claimId)
    {
        $claim = $this->getOneClaimCompleteOrIncompleteForMyInstitution($this->institution()->id, $claimId ,'incomplete');
        $datas = $this->getDataEdit($claim);
        $datas = Arr::except($datas, ['institutions']);
        return response()->json($datas,200);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param  $claimId
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $claimId)
    {
        $this->validate($request, $this->rules($request, true, false , true , true ));

        $claim = $this->getClaimUpdateForMyInstitution($this->institution()->id, $claimId, 'incomplete');

        $request->merge(['status' => $this->getStatus($request, true, false, true, true)]);

        $claim = $this->updateClaim($request, $claim, $this->staff()->id);

        $this->uploadAttachments($request, $claim);

        return response()->json($claim,201);
    }

}
