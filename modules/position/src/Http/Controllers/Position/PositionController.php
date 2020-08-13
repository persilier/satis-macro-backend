<?php

namespace Satis2020\Position\Http\Controllers\Position;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Position;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

class PositionController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-position')->only(['index']);
        $this->middleware('permission:show-position')->only(['show']);
        $this->middleware('permission:store-position')->only(['store']);
        $this->middleware('permission:update-position')->only(['update']);
        $this->middleware('permission:destroy-position')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Position::all(), 200);
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
            'name' => ['required', new TranslatableFieldUnicityRules('positions', 'name')],
            'description' => 'nullable',
        ];

        $this->validate($request, $rules);

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
        return response()->json($position, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function edit(Position $position)
    {

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
            'name' => ['required', new TranslatableFieldUnicityRules('positions', 'name', 'id', "{$position->id}")],
            'description' => 'nullable',
        ];

        $this->validate($request, $rules);

        $position->update($request->only(['name', 'description', 'others']));

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
        $position->secureDelete('staffs');

        return response()->json($position, 200);
    }
}
