<?php

namespace Satis2020\UnitPackage\Http\Controllers\Unit;

use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Unit;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\UnitType;

class UnitController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Unit::with(['unit', 'institution'])->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json([
            'units' => UnitType::all(),
            'institutions' => Institution::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'unit_type_id' => 'required|exists:unit_types,id',
            'institution_id' => 'required|exists:institutions,id'
        ];

        $this->validate($request, $rules);

        $unit = Unit::create($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'others']));

        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function show(Unit $unit)
    {
        return response()->json($unit, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function edit(Unit $unit)
    {
        return response()->json([
            'unit' => $unit,
            'units' => UnitType::all(),
            'institutions' => Institution::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\Unit $unit
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Unit $unit)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'unit_type_id' => 'required|exists:unit_types,id',
            'institution_id' => 'required|exists:institutions,id'
        ];

        $this->validate($request, $rules);

        $unit->update($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'others']));

        return response()->json($unit, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\Unit $unit
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->json($unit, 200);
    }
}
