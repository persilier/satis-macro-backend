<?php

namespace Satis2020\UnitPackage\Http\Controllers\Unit;

use Illuminate\Http\Response;
use Satis\CountriesPackage\Facades\Country;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Unit;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Rules\StateExistRule;
use Satis2020\ServicePackage\Services\CountryService;

class UnitController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return response()->json(Unit::with(['unitType', 'institution','state'])->get(), 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CountryService $countryService
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CountryService $countryService)
    {
        return response()->json([
            'unitTypes' => UnitType::all(),
            'institutions' => Institution::all(),
            'countries'=>Country::getAllAfricaCountries()

        ], 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'unit_type_id' => 'required|exists:unit_types,id',
            'institution_id' => 'required|exists:institutions,id',
            'state_id'=>['nullable','numeric',new StateExistRule]
        ];

        $this->validate($request, $rules);

        $unit = Unit::create($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'others','state_id']));

        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Unit $unit
     * @return Response
     */
    public function show(Unit $unit)
    {
        return response()->json($unit, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Unit $unit
     * @param CountryService $countryService
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Unit $unit,CountryService $countryService)
    {
        return response()->json([
            'unit' => $unit->load('unitType', 'institution'),
            'unitTypes' => UnitType::all(),
            'institutions' => Institution::all(),
            'countries'=>Country::getAllAfricaCountries()

        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Unit $unit
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Unit $unit)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'unit_type_id' => 'required|exists:unit_types,id',
            'institution_id' => 'required|exists:institutions,id',
            'state_id'=>['nullable','numeric',new StateExistRule]
        ];

        $this->validate($request, $rules);

        $unit->update($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'others','state_id']));

        return response()->json($unit, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Unit $unit
     * @return Response
     * @throws \Exception
     */
    public function destroy(Unit $unit)
    {
        $unit->secureDelete('staffs','children','claims','treatments');

        return response()->json($unit, 200);
    }
}
