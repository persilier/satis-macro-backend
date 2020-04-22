<?php

namespace Satis2020\PositionPackage\Http\Controllers\Position;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Position;
use Illuminate\Http\Request;

class PositionController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Position::with(['institutions'])->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

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
            'institutions' => 'required|array'
        ];

        $this->validate($request, $rules);

        foreach ($request->institutions as $institution_id){
            Institution::findOrFail($institution_id);
        }

        $position = Position::create($request->only(['name', 'description', 'others']));
        $position->institutions()->sync($request->institutions);

        return response()->json($position, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function show(Position $position)
    {
        return response()->json($position->load('institutions'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function edit(Position $position)
    {
        return response()->json([
            'position' => $position->load('institutions'),
            'institutions' => Institution::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\Position $position
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Position $position)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'institutions' => 'required|array'
        ];

        $this->validate($request, $rules);

        foreach ($request->institutions as $institution_id){
            Institution::findOrFail($institution_id);
        }

        $position->update($request->only(['name', 'description', 'others']));
        $position->institutions()->sync($request->institutions);

        return response()->json($position, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\Position $position
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Position $position)
    {
        $position->delete();

        return response()->json($position, 200);
    }
}
