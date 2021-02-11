<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Traits\ClientTrait;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ClientController
 * @package Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients
 */
class ClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, ClientTrait, SecureDelete;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-client-from-any-institution')->only(['index']);
        $this->middleware('permission:store-client-from-any-institution')->only(['create', 'store']);
        $this->middleware('permission:show-client-from-any-institution')->only(['show']);
        $this->middleware('permission:update-client-from-any-institution')->only(['edit', 'update']);
        $this->middleware('permission:destroy-client-from-any-institution')->only(['destroy']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(ClientInstitution::with(
            'client.identite',
            'category_client',
            'institution',
            'accounts.accountType'
        )->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws RetrieveDataUserNatureException
     */
    public function create()
    {
        return response()->json([
            'client_institutions' => ClientInstitution::with(
                'client.identite',
                'category_client',
                'institution',
                'accounts.accountType'
            )->get(),
            'institutions' => Institution::all(),
            'accountTypes' => AccountType::all(),
            'clientCategories' => CategoryClient::all()
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rulesClient(true));

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number);

        if (!$verifyAccount['status']) {
            throw new CustomException($verifyAccount, 409);

            //return response()->json($verifyAccount, 409);
        }
        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $request->institution_id);

        if (!$verifyPhone['status']) {
            throw new CustomException($verifyPhone, 409);

            //return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $request->institution_id);

        if (!$verifyEmail['status']) {
            throw new CustomException($verifyEmail, 409);

            //return response()->json($verifyEmail, 409);
        }

        $identite = $this->storeIdentite($request);

        $client = $this->storeClient($request, $identite->id);

        $clientInstitution = $this->storeClientInstitution($request, $client->id, $request->institution_id);

        $account = $this->storeAccount($request, $clientInstitution->id);

        return response()->json($account, 201);
    }

    /**
     * Display the specified resource.
     * @param $accountId
     * @return JsonResponse
     */
    public function show($accountId)
    {
        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->find($accountId);

        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account))
            return $this->errorResponse("Compte inexistant", Response::HTTP_NOT_FOUND);

        return response()->json($account, 200);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param $accountId
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function edit($accountId)
    {
        $client = $this->getOneAccountClient($accountId);
        return response()->json([
            'client_institution' => $client,
            'account' => Account::with('AccountType')->find($accountId),
            'clients' => $this->getAllClientByInstitution($client->institution_id),
            'institutions' => Institution::all(),
            'accountTypes' => AccountType::all(),
            'clientCategories' => CategoryClient::all()
        ], 200);

    }


    /**
     * @param Request $request
     * @param $accountId
     * @return JsonResponse
     * @throws ValidationException
     * @throws CustomException
     */
    public function update(Request $request, $accountId)
    {
        $this->validate($request, $this->rulesClient(true));

        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->find($accountId);

        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account))
            return $this->errorResponse("Compte inexistant", Response::HTTP_NOT_FOUND);

        $client = $account->client_institution->client;

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $account->id);

        if (!$verifyAccount['status']) {

            throw new CustomException($verifyAccount, 409);
        }

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $request->institution_id, 'id', $client->client->identite->id);

        if (!$verifyPhone['status']) {

            $verifyPhone['message'] = "We can't perform your request. The phone number " . $verifyPhone['verify']['conflictValue'] . " belongs to someone else";
            throw new CustomException($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $request->institution_id, 'id', $client->client->identite->id);

        if (!$verifyEmail['status']) {

            $verifyEmail['message'] = "We can't perform your request. The email address " . $verifyEmail['verify']['conflictValue'] . " belongs to someone else";
            throw new CustomException($verifyEmail, 409);
        }

        $account->update($request->only(['number', 'account_type_id']));

        $client->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        return response()->json($client, 201);
    }


    /**
     * @param Account $account
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Account $account)
    {
        $account->secureDelete('claims');
        return response()->json($account, 201);
    }

}

