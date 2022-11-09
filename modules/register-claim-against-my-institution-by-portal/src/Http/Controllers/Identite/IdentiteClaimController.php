<?php

namespace Satis2020\RegisterClaimAgainstMyInstitutionByPortal\Http\Controllers\Identite;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\StaffManagement;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class IdentiteClaimController extends Controller
{
    use IdentityManagement, DataUserNature, VerifyUnicity, CreateClaim, Telephone, Notification;

    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Identite $identite
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request, Identite $identite)
    {
        if ($request->isNotFilled('amount_disputed') || $request->isNotFilled('amount_currency_slug')) {
            $request->request->remove('amount_disputed');
            $request->request->remove('amount_currency_slug');
        }

        if ($request->isNotFilled("account_number")){
            $request->request->remove("account_number");
        }

        $request->merge(['claimer_id' => $identite->id]);

        $rulesRequest = $this->rules($request);
        $rulesRequest['created_by'] = 'nullable';

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $rulesRequest);

        // create reference
        $request->merge(['reference' => $this->createReference($request->institution_targeted_id)]);

        // Verify phone number and email unicity
//        $this->handleIdentityPhoneNumberAndEmailVerificationStore($request, $identite->id);
//        $this->updateIdentity($request, $identite);

        $status = $this->getStatus($request, false);

        // Check if the claim is complete
        $request->merge(['status' => $status['status']]);

        $claim = $this->createClaim($request, true);

        $this->attachFilesToClaimBase64($claim, $request);

        return response()->json([ 'claim' => $claim, 'errors' => $status['errors']], 201);

    }

}
