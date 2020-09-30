<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\Clients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Traits\ClientTrait;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class ClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, ClientTrait, SecureDelete;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-client-from-my-institution')->only(['index']);
        $this->middleware('permission:store-client-from-my-institution')->only(['create','store']);
        $this->middleware('permission:show-client-from-my-institution')->only(['show']);
        $this->middleware('permission:update-client-from-my-institution')->only(['edit','update']);
        $this->middleware('permission:destroy-client-from-my-institution')->only(['destroy']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        $institution = $this->institution();
        $clients = $this->getAllClientByInstitution($institution->id);
        return response()->json($clients, 200);
    }


    /**
     * @return JsonResponse
     */
    public function create(){
        $institution = $this->institution();
        return response()->json([
            'client_institutions' => $this->getAllClientByInstitution($institution->id),
            'accountTypes' => AccountType::all(),
            'clientCategories'=> CategoryClient::all()
        ],200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rulesClient());

        $institution = $this->institution();

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number);

        if (!$verifyAccount['status']) {

            throw new CustomException($verifyAccount, 409);

        }

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $institution->id);

        if (!$verifyPhone['status']) {

            throw new CustomException($verifyPhone, 409);

        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $institution->id);

        if (!$verifyEmail['status']) {

            throw new CustomException($verifyEmail, 409);
        }

        $identite = $this->storeIdentite($request);

        $client = $this->storeClient($request, $identite->id);

        $clientInstitution = $this->storeClientInstitution($request, $client->id, $institution->id);

        $account = $this->storeAccount($request, $clientInstitution->id);

        return response()->json($account, 201);
    }


    /**
     * @param $clientId
     * @return JsonResponse
     */
    public function show($clientId)
    {
        $institution = $this->institution();
        $client = $this->getOneClientByInstitution($institution->id, $clientId);
        return response()->json($client, 200);
    }


    /**
     * @param $accountId
     * @return JsonResponse
     */
    public function edit($accountId)
    {

        $institution = $this->institution();

        $client = $this->getOneAccountClientByInstitution($institution->id, $accountId);
        return response()->json([
            'client_institution' => $client,
            'AccountTypes' => AccountType::all(),
            'clientCategories'=> CategoryClient::all()
        ],200);

    }


    /**
     * @param Request $request
     * @param $accountId
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $accountId)
    {
        $this->validate($request, $this->rulesClient());

        $institution = $this->institution();

        $client = $this->getOneAccountClientByInstitution($institution->id, $accountId);

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $client->accounts[0]->id);

        if (!$verifyAccount['status']) {

            throw new CustomException($verifyAccount, 409);
        }

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $institution->id, 'id', $client->client->identite->id);

        if (!$verifyPhone['status']) {

            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            throw new CustomException($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $institution->id,'id', $client->client->identite->id);

        if (!$verifyEmail['status']) {

            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            throw new CustomException($verifyEmail, 409);
        }

        $client->accounts[0]->update($request->only(['number', 'account_type_id']));

        $client->client->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        return response()->json($client, 201);
    }


    /**
     * @param Account $account
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Account $account)
    {
        $account->delete();
        return response()->json($account, 201);
    }

}

