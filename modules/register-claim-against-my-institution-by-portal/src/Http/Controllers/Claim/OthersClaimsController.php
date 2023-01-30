<?php

namespace Satis2020\RegisterClaimAgainstMyInstitutionByPortal\Http\Controllers\Claim;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class OthersClaimsController
 * @package Satis2020\RegisterClaimAgainstMyInstitutionByPortal\Http\Controllers\Claim
 */
class OthersClaimsController extends Controller
{
    use DataUserNature, IdentityManagement, CreateClaim, VerifyUnicity, Telephone, Notification;

    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function store(Request $request)
    {
        if ($request->isNotFilled('amount_disputed') || $request->isNotFilled('amount_currency_slug')) {
            $request->request->remove('amount_disputed');
            $request->request->remove('amount_currency_slug');
        }

        $rulesRequest = $this->rules($request);

        $rulesRequest['created_by'] = 'nullable';

        $identite = null;

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $rulesRequest);

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        // create reference
        $request->merge(['reference' => $this->createReference($request->institution_targeted_id)]);

        // create claimer if claimer_id is null
        if (is_null($request->claimer_id)) {

            if (!$identite = $this->handleUnicityWithoutConflit($request)) {

                $claimer = $this->createIdentity($request);

            } else {

                $claimer = Identite::find($identite->id);
            }

            $request->merge(['claimer_id' => $claimer->id]);
        }

        // Check if the claim is complete
        $statusOrErrors = $this->getStatus($request);
        $request->merge(['status' => $statusOrErrors['status']]);

        $claim = $this->createClaim($request);

        return response()->json(['claim' => $claim, 'errors' => $statusOrErrors['errors']], 201);

    }

}
