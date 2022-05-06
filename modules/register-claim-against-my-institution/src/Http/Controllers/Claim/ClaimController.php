<?php

namespace Satis2020\RegisterClaimAgainstMyInstitution\Http\Controllers\Claim;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class ClaimController
 * @package Satis2020\RegisterClaimAgainstMyInstitution\Http\Controllers\Claim
 */
class ClaimController extends ApiController
{

    use IdentityManagement, DataUserNature, VerifyUnicity, CreateClaim, Telephone;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-claim-against-my-institution')->only(['store', 'create']);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function create()
    {
        $institution = $this->institution();

        return response()->json([
            'claimCategories' => ClaimCategory::all(),
            'units' => $institution->units()
                ->whereHas('unitType', function ($q) {
                    $q->where('can_be_target', true);
                })->get(),
            'channels' => Channel::all(),
            'currencies' => Currency::all()
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws ValidationException
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {

        $request->merge(['created_by' => $this->staff()->id]);
        $request->merge(['institution_targeted_id' => $this->institution()->id]);

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rules($request));

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

        return response()->json(['claim' => $claim, 'errors' => $statusOrErrors['errors']], 201);

    }

}
