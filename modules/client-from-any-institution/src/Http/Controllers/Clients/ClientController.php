<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\TypeClient;
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
        /*$this->middleware('auth:api');
        $this->middleware('permission:list-client-from-any-institution')->only(['index']);
        $this->middleware('permission:create-client-from-any-institution')->only(['store']);
        $this->middleware('permission:show-client-from-any-institution')->only(['show']);
        $this->middleware('permission:update-client-from-any-institution')->only(['update']);
        $this->middleware('permission:delete-client-from-any-institution')->only(['destroy']);*/
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return response()->json(Client::with(['identite','type_client', 'category_client', 'accounts'])->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(){
        return response()->json([
            'institutions'=> Institution::all(),
            'clientTypes' => TypeClient::all(),
            'clientCategories'=> CategoryClient::all()
        ],200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
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
            'type_clients_id' => 'required|exists:type_clients,id',
            'category_clients_id' => 'required|exists:category_clients,id',
            'institution_id' => 'required|exists:institutions,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];
        $this->validate($request, $rules);

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
        $verifyAccount = $this->handleAccountVerification($request->number,$request->institution_id);
        if (!$verifyAccount['status']) {
            return response()->json($verifyAccount, 409);
        }


        $identite = Identite::create($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $client = Client::create([
            'type_clients_id'       => $request->type_clients_id,
            'category_clients_id'   => $request->category_clients_id,
            'identites_id'          => $identite->id,
            'others'                => $request->others
        ]);

        $account = Account::create([
            'institution_id'   => $request->institution_id,
            'client_id'        => $client->id,
            'number'           => $request->number
        ]);
        return response()->json($client->load('identite','type_client', 'category_client', 'accounts'), 200);
    }

    /**
     * Display the specified resource.
     * @param $client
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Client $client)
    {
        return response()->json($client->load('identite','type_client', 'category_client', 'accounts'), 200);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param \Satis2020\ServicePackage\Models\Client $client
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, Client $client)
    {
        return response()->json([
            'client' => $client->load('identite','type_client', 'category_client', 'accounts'),
            'institutions' => Institution::all(),
            'clientTypes' => TypeClient::all(),
            'clientCategories'=> CategoryClient::all()
        ],200);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $account
     * @return \Illuminate\Http\JsonResponse|ClientResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $account)
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
            'type_clients_id' => 'required|exists:type_clients,id',
            'category_clients_id' => 'required|exists:category_clients,id',
            'institution_id' => 'required|exists:institutions,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];

        $this->validate($request, $rules);

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $account->client->identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $account->client->identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyEmail, 409);
        }

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $request->institution_id, $account->client->id);
        if (!$verifyAccount['status']) {
            return response()->json($verifyAccount, 409);
        }

        $account->update($request->only(['number','others']));

        $account->client->update($request->only(['type_clients_id', 'category_clients_id','others']));

        $account->client->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        return response()->json($account, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $client
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Client $client)
    {
        $client->secureDelete('accounts');
        return response()->json($client, 201);
    }

}

