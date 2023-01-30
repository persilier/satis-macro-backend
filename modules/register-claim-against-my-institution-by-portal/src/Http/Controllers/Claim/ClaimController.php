<?php

namespace Satis2020\RegisterClaimAgainstMyInstitutionByPortal\Http\Controllers\Claim;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class ClaimController
 * @package Satis2020\RegisterClaimAgainstMyInstitutionByPortal\Http\Controllers\Claim
 */
class ClaimController extends Controller
{
    use DataUserNature, IdentityManagement, CreateClaim, VerifyUnicity, Telephone, Notification;

    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function create(Institution $institution)
    {
        return response()->json([
            'claimCategories' => ClaimCategory::with('claimObjects')->get(),
            'units' => $institution->units()
                ->whereHas('unitType', function ($q) {
                    $q->where('can_be_target', true);
                })->get(),
            'channels' => Channel::all(),
            'currencies' => Currency::all()
        ], 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function store(Request $request)
    {
        if ($request->isNotFilled('amount_disputed') || $request->isNotFilled('amount_currency_slug')) {
            $request->request->remove('amount_disputed');
            $request->request->remove('amount_currency_slug');
        }

        if ($request->isNotFilled("account_number")){
            $request->request->remove("account_number");
        }

        $rulesRequest = $this->rules($request);
        $rulesRequest['created_by'] = 'nullable';
        $this->convertEmailInStrToLower($request);

        $this->validate($request, $rulesRequest);

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        // create reference
        $request->merge(['reference' => $this->createReference($request->institution_targeted_id)]);

        // create claimer if claimer_id is null
        if ($request->isNotFilled('claimer_id')) {
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

        $this->attachFilesToClaimBase64($claim, $request);

        return response()->json(['claim' => $claim, 'errors' => $statusOrErrors['errors']], 201);

    }

}
