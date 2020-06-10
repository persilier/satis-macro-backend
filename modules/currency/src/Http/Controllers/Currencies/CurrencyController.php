<?php

namespace Satis2020\Currency\Http\Controllers\Currencies;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Currency;
class CurrencyController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-currency')->only(['index']);
        $this->middleware('permission:store-currency')->only(['store']);
        $this->middleware('permission:show-currency')->only(['show']);
        $this->middleware('permission:update-currency')->only(['update']);
        $this->middleware('permission:destroy-currency')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json(Currency::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'iso_code' => 'required|string',
        ];
        $this->validate($request, $rules);
        $currencies = Currency::create($request->only(['name', 'iso_code']));
        return response()->json($currencies, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param Currency $currency
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Currency $currency)
    {
        return response()->json($currency, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Currency $currency
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Currency $currency)
    {
        $rules = [
            'name' => 'required|string',
            'iso_code' => 'required|string',
        ];
        $this->validate($request, $rules);
        $currency->slug = null;
        $currency->update(['name'=> $request->name, 'iso_code'=> $request->iso_code]);
        return response()->json($currency, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Currency $currency
     * @return $currency
     */
    public function destroy(Currency $currency)
    {
        $currency->delete();
        return response()->json($currency, 200);
    }
}
