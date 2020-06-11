<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\Clients;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Rules\EmailValidationRules;
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
     * Display a listing of the resource.
     *
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        $institution = $this->institution();
        $clients = $this->getAllClientByInstitution($institution->id);
        return response()->json($clients, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws RetrieveDataUserNatureException
     */
    public function create(){
        $institution = $this->institution();
        return response()->json([
            'client_institutions' => $this->getAllClientByInstitution($institution->id),
            'AccountTypes' => AccountType::all(),
            'clientCategories'=> CategoryClient::all()
        ],200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {
        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => 'required|array',
            'email' => [
                'required', 'array', new EmailValidationRules,
            ],
            'ville' => 'required|string',
            'number' => 'required|string',
            'account_type_id' => 'required|exists:account_types,id',
            'category_client_id' => 'required|exists:category_clients,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];

        $this->validate($request, $rules);

        $institution = $this->institution();

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone');
        if (!$verifyPhone['status']) {
            return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email');
        if (!$verifyEmail['status']) {
            return response()->json($verifyEmail, 409);
        }

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $institution->id);
        if (!$verifyAccount['status']) {
            return response()->json($verifyAccount, 409);
        }



        $identite = Identite::create($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $client = Client::create([
            'identites_id'          => $identite->id,
            'others'                => $request->others
        ]);

        $client_institution = ClientInstitution::create([
            'client_id'             => $client->id,
            'category_client_id'    => $request->category_client_id,
            'institution_id'        => $institution->id
        ]);

        $account = Account::create([
            'client_institution_id'   => $client_institution->id,
            'account_type_id'  => $request->account_type_id,
            'number'           => $request->number
        ]);
        return response()->json($account, 201);
    }

    /**
     * Display the specified resource.
     * @param $client
     * @return \Illuminate\Http\JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function show($client)
    {
        $institution = $this->institution();
        $client = $this->getOneClientByInstitution($institution->id, $client);
        return response()->json($client, 200);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param $account
     * @return \Illuminate\Http\JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function edit($account)
    {

        $institution = $this->institution();

        $client = $this->getOneAccountClientByInstitution($institution->id, $account);
        return response()->json([
            'client_institution' => $client,
            'AccountTypes' => AccountType::all(),
            'clientCategories'=> CategoryClient::all()
        ],200);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $account
     * @return \Illuminate\Http\JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request,$account)
    {
        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => 'required|array',
            'email' => [
                'required', 'array', new EmailValidationRules,
            ],
            'ville' => 'required|string',
            'number' => 'required|string',
            'account_type_id' => 'required|exists:account_types,id',
            'category_client_id' => 'required|exists:category_clients,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];

        $this->validate($request, $rules);

        $institution = $this->institution();

        $client = $this->getOneAccountClientByInstitution($institution->id, $account);

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $client->client->identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $client->client->identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyEmail, 409);
        }


        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $institution->id, $client->accounts[0]->id);
        if (!$verifyAccount['status']) {
            return response()->json($verifyAccount, 409);
        }

        $client->accounts[0]->update($request->only(['number', 'account_type_id']));

        $client->client->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        return response()->json($client, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $client
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Account $account)
    {
        $account->delete();
        return response()->json($account, 201);
    }

}

