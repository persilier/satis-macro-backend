<?php

namespace Satis2020\UserPackage\Http\Controllers\Identite;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class IdentiteClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity;

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Identite $identite
     * @return ClientResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Identite $identite)
    {
        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => 'required|array',
            'email' => 'required|array',
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
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyEmail, 409);
        }

        // Client Account Number Unicity Verification
        $verifyAccountNumber = $this->handleClientIdentityVerification($request->account_number, 'clients', 'account_number', 'account_number');
        if (!$verifyAccountNumber['status']) {
            return response()->json($verifyAccountNumber, 409);
        }

        $identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'id_card', 'ville', 'other_attributes']));
        $client = Client::create([
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

}
