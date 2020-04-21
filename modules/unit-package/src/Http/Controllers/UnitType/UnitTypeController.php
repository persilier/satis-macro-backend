<?php

namespace Satis2020\UnitPackage\Http\Controllers\UnitType;

use Satis2020\ServicePackage\Models\UnitType;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;

class UnitTypeController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(UnitType::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'description' => 'required'
        ];

        $this->validate($request, $rules);

        $unitType = UnitType::create($request->only(['name', 'description', 'others']));

        return response()->json($unitType, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UnitType  $unitType
     * @return \Illuminate\Http\Response
     */
    public function show(UnitType $unitType)
    {
        return response()->json($unitType, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UnitType  $unitType
     * @return \Illuminate\Http\Response
     */
    public function edit(UnitType $unitType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\UnitType $unitType
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, UnitType $unitType)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $this->validate($request, $rules);

        $unitType->update($request->only(['name', 'description', 'others']));

        return response()->json($unitType, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\UnitType $unitType
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(UnitType $unitType)
    {
        $unitType->delete();

        return response()->json($unitType, 200);
    }
}
