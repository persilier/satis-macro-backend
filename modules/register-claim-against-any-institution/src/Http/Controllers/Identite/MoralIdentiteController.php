<?php

namespace Satis2020\RegisterClaimAgainstAnyInstitution\Http\Controllers\Identite;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\StaffManagement;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class MoralIdentiteController extends ApiController
{
    use IdentityManagement, DataUserNature, VerifyUnicity, CreateClaim, Telephone;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-claim-against-any-institution')->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Identite $identite
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
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
