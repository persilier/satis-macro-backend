<?php

namespace Satis2020\RegisterClaimAgainstAnyInstitution\Http\Controllers\Claim;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Rules\ClientBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\UnitCanBeTargetRules;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Symfony\Component\HttpFoundation\JsonResponse;


class ClaimController extends ApiController
{

    use IdentityManagement, DataUserNature, VerifyUnicity, CreateClaim, Telephone;



    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-claim-against-any-institution')->only(['store', 'create']);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {

        $request->merge(['created_by' => $this->staff()->id]);

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesForMoralEntity($request));

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        // create reference
        $request->merge(['reference' => $this->createReference($request->institution_targeted_id)]);

        // create claimer if claimer_id is null
        if (is_null($request->claimer_id)) {
            // Verify phone number and email unicity
            $this->handleIdentityPhoneNumberAndEmailVerificationStore($request);
            // register claimer
            $claimer = $this->createIdentity($request);
            $request->merge(['claimer_id' => $claimer->id]);
        }

        // Check if the claim is complete
        $statusOrErrors = $this->getStatus($request);
        $request->merge(['status' => $statusOrErrors['status']]);

        $claim = $this->createClaim($request);

        return response()->json(['claim' => $claim, 'errors' => $statusOrErrors['errors']], 201);
    }
}
