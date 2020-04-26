<?php

namespace Satis2020\ClientPackage\Http\Controllers\Clients;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;

class ClientController extends ApiController
{
    use IdentiteVerifiedTrait;
    public function __construct()
    {
        /*
       $this->middleware('permission:can-create-client')->only(['store']);*/
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ClientResource
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'identites_id' => 'required|exists:identites,id',
            'account_number' => 'required|string',
            'type_clients_id' => 'required|exists:type_clients,id',
            'category_clients_id' => 'required|exists:category_clients,id',
            'units_id' => 'required|exists:units,id',
            'institutions_id' => 'required|exists:institutions,id',
            'others' => 'array'
        ];
        $this->validate($request, $rules);

        $valid_client = $this->IsValidClientIdentite($request->type_clients_id, $request->category_clients_id, $request->units_id, $request->institutions_id);
        if(false == $valid_client['valide'])
            return $this->errorResponse($valid_client['message'], 400);

        $valid_exist = $this->IsValidClient($request->account_number, $request->institutions_id, $request->identites_id, $request->all());
        if(false == $valid_exist['valide'])
            return $this->errorResponse($valid_exist['message'], 400);

        if($client_exist = Client::where('institutions_id', $request->institutions_id)->where('identites_id', $request->identites_id)->where('account_number', $request->account_number)->first())
            return $this->errorResponse('Ce compte client existe dans l\'institution sélectionnée', 400);
        $client = Client::create([
            'account_number'        => [$request->account_number],
            'type_clients_id'       => $request->type_clients_id,
            'category_clients_id'   => $request->category_clients_id,
            'identites_id'          => $request->identites_id,
            'units_id'              => $request->units_id,
            'institutions_id'       => $request->institutions_id,
            'others'                => $request->others
        ]);
        return new ClientResource($client);
    }

}

