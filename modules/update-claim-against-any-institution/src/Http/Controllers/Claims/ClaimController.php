<?php

namespace Satis2020\UpdateClaimAgainstAnyInstitution\Http\Controllers\Claims;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\UpdateClaim;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class ClaimController
 * @package Satis2020\UpdateClaimAgainstAnyInstitution\Http\Controllers\Claims
 */
class ClaimController extends ApiController
{
    use  CreateClaim, UpdateClaim, VerifyUnicity;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-incomplete-against-any-institution')->only(['index']);
        $this->middleware('permission:show-claim-incomplete-against-any-institution')->only(['show']);
        $this->middleware('permission:update-claim-incomplete-against-any-institution')->only(['update']);
    }


    /**
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
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
     * @param $claimId
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function edit($claimId)
    {

        $claim = $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claimId ,'incomplete');
        $claims = $this->getDataEdit($claim);
        return response()->json($claims,200);
    }


    /**
     * @param Request $request
     * @param $claimId
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function update(Request $request, $claimId)
    {

        $institution = $this->institution();

        $institution->load('institutionType');

        $claim = $this->getClaimUpdate($institution->id, $claimId, 'incomplete');

        $request->merge(['claimer_id' => $claim->claimer_id]);

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesCompletion($request, $claim));

        $this->validateUnicityIdentiteCompletion($request, $claim);

        $claim = $this->updateClaim($request, $claim, $this->staff()->id);

        $this->uploadAttachments($request, $claim);

        return response()->json($claim,201);
    }

}
