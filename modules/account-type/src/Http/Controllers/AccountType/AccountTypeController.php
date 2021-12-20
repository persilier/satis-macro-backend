<?php

namespace Satis2020\AccountType\Http\Controllers\AccountType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Satis2020\ServicePackage\Traits\SecureDelete;


/**
 * Class AccountTypeController
 * @package Satis2020\AccountType\Http\Controllers\AccountTpe
 */
class AccountTypeController extends ApiController
{
    use SecureDelete;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-account-type')->only(['index']);
        $this->middleware('permission:store-account-type')->only(['store']);
        $this->middleware('permission:show-account-type')->only(['show']);
        $this->middleware('permission:update-account-type')->only(['update']);
        $this->middleware('permission:destroy-account-type')->only(['destroy']);
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(AccountType::all(), 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('account_types', 'name')],
            'description' => 'nullable|string',
        ];

        $this->validate($request, $rules);
        $accountType = AccountType::create($request->only(['name', 'description']));
        return response()->json($accountType, 201);

    }


    /**
     * @param AccountType $accountType
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(AccountType $accountType)
    {
        return response()->json($accountType, 200);
    }


    /**
     * @param Request $request
     * @param AccountType $accountType
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, AccountType $accountType)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('account_types', 'name', 'id', "{$accountType->id}")],
            'description' => 'nullable|string',
        ];
        $this->validate($request, $rules);

        $accountType->update($request->only(['name', 'description']));
        return response()->json($accountType, 201);
    }


    /**
     * @param AccountType $accountType
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\SecureDeleteException
     */
    public function destroy(AccountType $accountType)
    {
        $accountType->secureDelete('accounts');
        return response()->json($accountType, 200);
    }
}
