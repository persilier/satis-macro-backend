<?php

namespace Satis2020\UpdateClaimWithoutClient\Http\Controllers\Claims;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\UpdateClaim;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class ClaimController
 * @package Satis2020\UpdateClaimWithoutClient\Http\Controllers\Claims
 */
class ClaimController extends ApiController
{
    use  CreateClaim, UpdateClaim, VerifyUnicity;
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
     * @return JsonResponse
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
     */
    public function edit($claimId)
    {
        $claim = $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claimId ,'incomplete');
        $datas = $this->getDataEditWithoutClient($claim);
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
        $claim = $this->getClaimUpdate($this->institution()->id, $claimId, 'incomplete');

        $request->merge(['claimer_id' => $claim->claimer_id]);

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesCompletion($request, $claim, true , false, false));

        $this->validateUnicityIdentiteCompletion($request, $claim);

        $claim = $this->updateClaim($request, $claim, $this->staff()->id);

        $this->uploadAttachments($request, $claim);

        return response()->json($claim,201);
    }

}
