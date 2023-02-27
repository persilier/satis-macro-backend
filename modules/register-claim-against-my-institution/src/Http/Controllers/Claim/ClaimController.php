<?php

namespace Satis2020\RegisterClaimAgainstMyInstitution\Http\Controllers\Claim;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Currency;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Traits\ScanFileClaimPrediction;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\ClaimsCategoryPrediction;
use Satis2020\ServicePackage\Traits\ClaimsCategoryObjectPrediction;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;


/**
 * Class ClaimController
 * @package Satis2020\RegisterClaimAgainstMyInstitution\Http\Controllers\Claim
 */
class ClaimController extends ApiController
{

    use IdentityManagement, DataUserNature, VerifyUnicity, CreateClaim, ClaimsCategoryObjectPrediction, Telephone, ScanFileClaimPrediction;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-claim-against-my-institution')->only(['store', 'create', 'storeFromFile']);
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
        //$request->merge(['claim_object_id' => $objectId]);
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

    public function storeFromFile(Request $request)
    {
        $this->validate($request, $this->rulesInformationExtraction());
        $response  = $this->informationExtraction($request->file);
        $request->merge(['created_by' => $this->staff()->id]);
        $request->merge(['institution_targeted_id' => $this->institution()->id]);
        $channel = Channel::whereSlug("email")->first();
        $channel2 = Channel::whereSlug("sms")->first();

        $claimObject = $this->allClaimsCategoryObjectPrediction($response[" quelle est la description de la réclamation?"]["answer"]);
        $claimObjectId = null;
        $claimObject = ClaimObject::query()->where("name->" . \App::getLocale(), $claimObject)->first();
        if ($claimObject) {
            $claimObjectId = $claimObject->id;
        }

        $request->merge([
            "description" => $response["body"]["answer"],
            "request_channel_slug" => $channel->slug,
            "response_channel_slug" => $channel2->slug,
            "lieu" => $response[" quel est la ville du client ?"]["answer"],
            "event_occured_at" => $response["quel est la date de l'évènement?"]["answer"],
            "amount_disputed" => $response[" quel est le montant réclamé ?"]["answer"],
            "firstname" => $response["quel est le nom du client ?"]["answer"],
            "lastname" => $response[" quel est le prénom du client ?"]["answer"],
            "telephone" => [$response["quel est le numéro de téléphone du client ?"]["answer"]],
            "email" => [$response[" quel est l'email du client ?"]["answer"]],
            "sexe" => "A",
            "is_revival" => false,
            "claim_object_id" => $claimObjectId
        ]);

        $this->convertEmailInStrToLower($request);
        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);
        $request->merge(['reference' => $this->createReference($request->institution_targeted_id)]);
        // Verify phone number and email unicity
        $resultHandle = $this->existIdentityPhoneNumberAndEmailVerificationStore($request);

        if ($resultHandle["exist"]) {
            $request->merge(['claimer_id' => $resultHandle["data"]["entity"]->id]);
        } else {
            // register claimer
            $claimer = $this->createIdentity($request);
            $request->merge(['claimer_id' => $claimer->id]);
        }

        $statusOrErrors = $this->getStatus($request);
        $request->merge(['status' => $statusOrErrors['status']]);

        $claim = $this->createClaim($request);
        return response()->json(['claim' => $claim, 'errors' => $statusOrErrors['errors']], 201);
    }

    public function getClaimsCategoryPrediction($description)
    {

        return $this->allClaimsCategoryPrediction($description);
    }
}
