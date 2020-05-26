<?php

namespace Satis2020\AnyInstitutionUnit\Http\Controllers\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;

class UnitController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        /*$this->middleware('permission:can-list-any-unit')->only(['index']);
        $this->middleware('permission:can-create-any-unit')->only(['store']);
        $this->middleware('permission:can-show-any-unit')->only(['show']);
        $this->middleware('permission:can-update-any-unit')->only(['update']);
        $this->middleware('permission:can-delete-any-unit')->only(['destroy']);*/

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Unit::with(['unitType', 'institution', 'parent', 'children', 'lead'])->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json([
            'unitTypes' => UnitType::all(),
            'institutions' => Institution::all(),
            'parents' => Unit::all()
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
            'institution_id' => 'required|exists:institutions,id',
            'parent_id' => [Rule::exists('units', 'id')->where(function ($query) use ($request) {
                $query->where('institution_id', $request->institution_id);
            })],
        ];
        $this->validate($request, $rules);

        $unit = Unit::create($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'parent_id', 'others']));

        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Unit $any_unit
     * @return \Illuminate\Http\Response
     */
    public function show(Unit $any_unit)
    {
        return response()->json($any_unit, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Unit $any_unit
     * @return \Illuminate\Http\Response
     */
    public function edit(Unit $any_unit)
    {
        return response()->json([
            'unit' => $any_unit->load('unitType', 'institution', 'parent', 'children', 'lead'),
            'unitTypes' => UnitType::all(),
            'institutions' => Institution::all(),
            'lead' => Staff::all(),
            'parent' => Unit::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Unit $any_unit
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Unit $any_unit)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'unit_type_id' => 'required|exists:unit_types,id',
            'institution_id' => 'required|exists:institutions,id',
            'lead_id' => [Rule::exists('staff', 'id')->where(function ($query) use ($request, $any_unit) {
                $query->where('institution_id', $request->institution_id)->where('unit_id', $any_unit->id);
            })],
            'parent_id' =>  [Rule::exists('units', 'id')->where(function ($query) use ($request) {
                $query->where('institution_id', $request->institution_id);
            })],
        ];

        $this->validate($request, $rules);

        $any_unit->update($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'lead_id', 'parent_id', 'others']));

        return response()->json($any_unit, 201);
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
