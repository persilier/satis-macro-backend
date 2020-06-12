<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\Accounts;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Account;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\ClientTrait;

class AccountClientController extends ApiController
{
    use ClientTrait;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-client-from-any-institution')->only(['store']);
    }

    /**
     * Store a newly created resource in storage
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, $clientId)
    {
        $this->validate($request, $this->rulesAccount(true));

        $clientInstitution = ClientInstitution::where('institution_id', $request->institution_id)->where('client_id', $clientId)->firstOrFail();

        // Account Number Verification
        $verifyAccount = $this->handleAccountClient($request->number, $clientInstitution->id);
        if (!$verifyAccount['status']) {
            return response()->json($verifyAccount, 409);
        }


        $account = $this->storeAccount($request, $clientInstitution->id);

        return response()->json($account, 201);
    }

}
