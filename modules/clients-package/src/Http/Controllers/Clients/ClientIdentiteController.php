<?php

namespace Satis2020\ClientPackage\Http\Controllers\Clients;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ClientPackage\Http\Resources\ClientCollection;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;

class ClientIdentiteController extends ApiController
{
    use IdentiteVerifiedTrait;

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-client')->only(['index']);
        $this->middleware('permission:can-create-client')->only(['store']);
        $this->middleware('permission:can-show-client')->only(['show']);
        $this->middleware('permission:can-update-client')->only(['update']);
        $this->middleware('permission:can-delete-client')->only(['destroy']);*/
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return ClientResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F'])],
            'email' => 'email',
            'telephone' => 'required',
            'ville' => 'required|string',
            'id_card' => 'required|string',
            'account_number' => 'required|string',
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

        $identite_exist = $this->IdentiteExist($request->email, $request->telephone, $request->only(['id_card','account_number','type_clients_id', 'category_clients_id', 'units_id','institutions_id','others']));
        if(false == $identite_exist['valide'])
            return response()->json(['data' => $identite_exist['message'], 'code' => 400], 400);

        $identite = Identite::create($request->only(
            ['firstname', 'lastname', 'sexe', 'telephone', 'ville', 'email', 'other_attributes']
        ));

        $client = Client::create([
            'id_card'               => $request->id_card,
            'account_number'        => $request->account_number,
            'type_clients_id'       => $request->type_clients_id,
            'category_clients_id'   => $request->category_clients_id,
            'identites_id'          => $identite->id,
            'units_id'              => $request->units_id,
            'institutions_id'       => $request->institutions_id,
            'others'                => $request->others
        ]);
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
            'lastname' => 'required|string|max:50',
            'firstname' => 'required|string|max:50',
            'gender' => 'required|string|max:50',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:50',
            'ville' => 'required|string|max:255',
            'id_card' => 'required|string',
            'is_client' => 'required|string',
            'account_number' => 'nullable|string',
            'type_clients_id' => 'nullable|exists:type_clients,id',
            'category_clients_id' => 'nullable|exists:category_clients,id',
            'units_id' => 'nullable|exists:units,id',
            'institutions_id' => 'required|exists:institutions,id',
            'others' => 'array',
        ];
        $this->validate($request, $rules);

        if(true == $request->is_client){
            if(!$type_client = TypeClient::whereId($request->type_clients_id)
                ->whereInstitutions_id($request->institutions_id)->first())
                return $this->errorResponse('Le type de client n\'existe pas dans l\'institution sélectionnée.', 400);

            if(!$category_clients = CategoryClient::whereId($request->category_clients_id)
                ->whereInstitutions_id($request->institutions_id)->first())
                return $this->errorResponse('La catégorie de client n\'existe pas dans l\'institution sélectionnée.', 400);

            if(!$units = Unit::whereId($request->units_id)
                ->whereInstitutions_id($request->institutions_id)->first())
                return $this->errorResponse('Cette unité n\'existe pas dans l\'institution sélectionnée.', 400);

        }

        if($client_exist = Client::where('phone',$request->phone)->where('email',$request->phone)
            ->where('institutions_id',$request->institutions_id)->firstOrFail())
            return $this->errorResponse('Ce client existe déjà pour dans l\'institution sélectionnée.', 400);

        $client->update($request->all());
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
        $client->delete();
        return new ClientResource($client);
    }
}

