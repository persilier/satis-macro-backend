<?php

namespace Satis2020\WithoutLinkWithInstitutionUnit\Http\Controllers\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

class UnitController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-without-link-unit')->only(['index']);
        $this->middleware('permission:store-without-link-unit')->only(['create','store']);
        $this->middleware('permission:show-without-link-unit')->only(['show']);
        $this->middleware('permission:update-without-link-unit')->only(['edit','update']);
        $this->middleware('permission:destroy-without-link-unit')->only(['destroy']);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Unit::with(['unitType', 'parent', 'children', 'lead.identite'])->get(), 200);
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
            'units' => Unit::all(),
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
            'name' => ['required', new TranslatableFieldUnicityRules('units', 'name')],
            'description' => 'nullable',
            'unit_type_id' => 'required|exists:unit_types,id',
            'parent_id' => 'exists:units,id'
        ];

        $this->validate($request, $rules);

        $unit = Unit::create($request->only(['name', 'description', 'unit_type_id', 'parent_id', 'others']));

        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function show(Unit $unit)
    {
        return response()->json($unit->load('unitType', 'parent', 'children', 'lead.identite'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function edit(Unit $unit)
    {
        return response()->json([
            'unit' => $unit->load('unitType', 'parent', 'children', 'lead.identite'),
            'units' => Unit::all(),
            'unitTypes' => UnitType::all(),
            'leads' => Staff::with('identite')->where('unit_id', $unit->id)->get(),
            'parents' => Unit::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Unit $unit
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Unit $unit)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('units', 'name', 'id', "{$unit->id}")],
            'description' => 'nullable',
            'unit_type_id' => 'required|exists:unit_types,id',
            'lead_id' => 'sometimes|',Rule::exists('staff', 'id')->where(function ($query) use ($unit) {
                $query->where('unit_id', $unit->id);
            }),
            'parent_id' => 'exists:units,id'
        ];

        $this->validate($request, $rules);

        $unit->update($request->only(['name', 'description', 'unit_type_id', 'lead_id', 'parent_id', 'others']));

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
        $unit->secureDelete('staffs', 'children');
        return response()->json($unit, 200);
    }
}
