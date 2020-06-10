<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\Accounts;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ClientPackage\Http\Resources\Client as ClientResource;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Satis2020\ServicePackage\Traits\ClientTrait;

class AccountClientController extends ApiController
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
    public function store(Request $request, $client)
    {
        $rules = [
            'number' => 'required|string',
            'account_type_id' => 'required|exists:account_types,id',
        ];

        $this->validate($request, $rules);

        $institution = $this->institution();

        $client_institution = ClientInstitution::where('institution_id', $institution->id)->where('client_id', $client)->firstOrFail();

        // Account Number Verification
        $verifyAccount = $this->handleAccountClient($request->number, $client_institution->id);
        if (!$verifyAccount['status']) {
            return response()->json($verifyAccount, 409);
        }


        $account = Account::create([
            'client_institution_id'   => $client_institution->id,
            'account_type_id'  => $request->account_type_id,
            'number'           => $request->number
        ]);
        return response()->json($account, 200);
    }

}
