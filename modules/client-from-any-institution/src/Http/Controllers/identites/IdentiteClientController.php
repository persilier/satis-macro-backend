<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\Identites;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Satis2020\ServicePackage\Traits\ClientTrait;

class IdentiteClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, ClientTrait;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-client-from-my-institution')->only(['store']);
    }

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
            'email' => [
                'required', 'array', new EmailValidationRules,
            ],
            'ville' => 'required|string',
            'number' => 'required|string',
            'institution_id' => 'required|exists:institutions,id',
            'account_type_id' => 'required|exists:account_types,id',
            'category_client_id' => 'required|exists:category_clients,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];

        $this->validate($request, $rules);

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

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $request->institution_id);
        if (!$verifyAccount['status']) {
            return response()->json($verifyAccount, 409);
        }

        $identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));
        $client = Client::create([
            'identites_id'          => $identite->id,
            'others'                => $request->others
        ]);

        $client_institution = ClientInstitution::create([
            'client_id'             => $client->id,
            'category_client_id'    => $request->category_client_id,
            'institution_id'        => $request->institution_id
        ]);

        $account = Account::create([
            'client_institution_id'   => $client_institution->id,
            'account_type_id'  => $request->account_type_id,
            'number'           => $request->number
        ]);
        return response()->json($account, 200);
    }

}
