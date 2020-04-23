<?php

namespace Satis2020\ClientPackage\Http\Controllers\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ClientPackage\Http\Resources\ClientCollection;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\Unit;

class ClientController extends ApiController
{


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
        $client = Client::create($request->all());
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

