<?php

namespace Satis2020\ClientPackage\Http\Controllers\Clients;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mockery\Matcher\Type;
use Satis2020\ClientPackage\Http\Resources\ClientCollection;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Rules\EmailValidationRules;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class ClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        /*$this->middleware('permission:can-list-client-from-my-institution')->only(['index']);
        $this->middleware('permission:can-create-client-from-my-institution')->only(['store']);
        $this->middleware('permission:can-show-client-from-my-institution')->only(['show']);
        $this->middleware('permission:can-update-client-from-my-institution')->only(['update']);
        $this->middleware('permission:can-delete-client-from-my-institution')->only(['destroy']);*/

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return ClientCollection
     */
    public function index()
    {
        return new ClientCollection(Client::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $params = [];
        if($request->institution)
            $params['institution'] = $request->institution;

        if($request->type_unit)
            $params['type_unit'] = $request->type_unit;
        $data = $this->getDataForCreateEditClient($params);
        return response()->json($data,200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return ClientResource
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
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
            'id_card' => 'required|array',
            'account_number' => 'required|array',
            'type_clients_id' => 'required|exists:type_clients,id',
            'category_clients_id' => 'required|exists:category_clients,id',
            'units_id' => 'required|exists:units,id',
            'institutions_id' => 'required|exists:institutions,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];
        $this->validate($request, $rules);

        $valid_client = $this->IsValidClientIdentite($request->type_clients_id, $request->category_clients_id, $request->units_id, $request->institutions_id);
        if(false == $valid_client['valide'])
            return $this->errorResponse($valid_client['message'],400);

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

        // Client Account Number Unicity Verification
        $verifyAccountNumber = $this->handleClientIdentityVerification($request->account_number, 'clients', 'account_number', 'account_number');
        if (!$verifyAccountNumber['status']) {
            return response()->json($verifyAccountNumber, 409);
        }


        $identite = Identite::create($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'id_card', 'ville', 'other_attributes']));

        $client = Client::create([
            'account_number'        => $request->account_number,
            'type_clients_id'       => $request->type_clients_id,
            'category_clients_id'   => $request->category_clients_id,
            'identites_id'          => $identite->id,
            'units_id'              => $request->units_id,
            'institutions_id'       => $request->institutions_id,
            'others'                => $request->others
        ]);

        $this->activityLogService->store("Enregistrement d'un compte client",
            $this->institution()->id,
            $this->activityLogService::CREATED,
            'client',
            $this->user(),
            $client
        );

        return new ClientResource($client);
    }

    /**
     * Display the specified resource.
     *
     * @param Client $client
     * @return ClientResource
     */
    public function show(Client $client)
    {
        return new ClientResource($client);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param \Satis2020\ServicePackage\Models\Client $client
     * @return ClientResource
     */
    public function edit(Request $request, Client $client)
    {
        $params = [];
        if($request->institution)
            $params['institution'] = $request->institution;

        if($request->type_unit)
            $params['type_unit'] = $request->type_unit;
        $data = $this->getDataForCreateEditClient($params);
        return (new ClientResource($client))->additional($data);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Client $client
     * @return ClientResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Client $client)
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
            'id_card' => 'required|array',
            'account_number' => 'required|array',
            'type_clients_id' => 'required|exists:type_clients,id',
            'category_clients_id' => 'required|exists:category_clients,id',
            'units_id' => 'required|exists:units,id',
            'institutions_id' => 'required|exists:institutions,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];
        $this->validate($request, $rules);

        $valid_client = $this->IsValidClientIdentite($request->type_clients_id, $request->category_clients_id, $request->units_id, $request->institutions_id);
        if(false == $valid_client['valide'])
            return $this->errorResponse($valid_client['message'],400);


        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $client->identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $client->identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyEmail, 409);
        }

        // Client Account Number Unicity Verification
        $verifyAccountNumber = $this->handleClientIdentityVerification($request->account_number, 'clients', 'account_number' ,'account_number','id' , $client->id);
        if (!$verifyAccountNumber['status']) {
            $verifyAccountNumber['message'] = "We can't perform your request. The account number ".$verifyAccountNumber['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyAccountNumber, 409);
        }


        $client->update([
            'account_number' => $request->account_number,
            'type_clients_id' => $request->type_clients_id,
            'category_clients_id' => $request->category_clients_id,
            'units_id'  => $request->units_id,
            'institutions_id' => $request->institutions_id,
            'others' => $request->others
        ]);
        $client->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'id_card', 'other_attributes']));

        return new ClientResource($client);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Client $client
     * @return ClientResource
     * @throws \Exception
     */
    public function destroy(Client $client)
    {
        $client->secureDelete('client_institutions');
        return new ClientResource($client);
    }


    private function getDataForCreateEditClient($params){
        $institutions = Institution::all();
        $type_clients = New TypeClient;
        $category_clients = New CategoryClient;
        $type_units = New UnitType;
        $units = New Unit;

        if(isset($params['institution'])){
            $type_clients = $type_clients->where('institutions_id',$params['institution']);
            $category_clients = $category_clients->where('institutions_id',$params['institution']);
            $units = $units->where('institution_id',$params['institution']);
        }

        if(isset($params['type_unit'])){
            $units = $units->where('unit_type_id',$params['type_unit']);
        }

        return $data = [
            'institutions' => $institutions,
            'type_clients'=> $type_clients->get(),
            'category_clients'=> $category_clients->get(),
            'type_units'=> $type_units->get(),
            'units' => $units->get()
        ];
    }
    
}

