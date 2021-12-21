<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\Identites;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Satis2020\ServicePackage\Traits\ClientTrait;

class IdentiteClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, ClientTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-client-from-my-institution')->only(['store']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Identite $identite
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Identite $identite)
    {
        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesClient());

        $institution = $this->institution();
        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $institution->id,'id', $identite->id);
        if (!$verifyPhone['status']) {

            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            throw new CustomException($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $institution->id,'id', $identite->id);
        if (!$verifyEmail['status']) {
            
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            throw new CustomException($verifyEmail, 409);
        }

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $institution->id);
        if (!$verifyAccount['status']) {

            throw new CustomException($verifyAccount, 409);
        }

        $identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $client = $this->storeClient($request, $identite->id, true);

        $clientInstitution = $this->storeClientInstitution($request, $client->id, $institution->id, true);

        $account = $this->storeAccount($request, $clientInstitution->id);

        return response()->json($account, 201);
    }

}
