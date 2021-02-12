<?php

namespace Satis2020\TypeUnit\Http\Controllers\UnitType;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\UnitType;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Rules\FieldUnicityRules;
use Satis2020\ServicePackage\Rules\IsEditableRules;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

/**
 * Class UnitTypeController
 * @package Satis2020\TypeUnit\Http\Controllers\UnitType
 */
class UnitTypeController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-unit-type')->only(['index']);
        $this->middleware('permission:store-unit-type')->only(['store']);
        $this->middleware('permission:show-unit-type')->only(['show']);
        $this->middleware('permission:update-unit-type')->only(['edit','update']);
        $this->middleware('permission:destroy-unit-type')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(UnitType::with(['parent','children'])->get(), 200);
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

        $request->merge(['is_editable' => true]);

        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('unit_types', 'name')],
            'description' => 'nullable',
            'parent_id' => 'exists:unit_types,id',
            'is_editable' => ['required', Rule::in([true])],
            'can_be_target' => ['required', 'boolean'],
            'can_treat' => ['required', 'boolean'],
        ];

        $this->validate($request, $rules);
        $unitType = UnitType::create($request->only(['name', 'description','parent_id', 'others', 'is_editable', 'can_be_target', 'can_treat']));
        return response()->json($unitType, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param UnitType $unitType
     * @return \Illuminate\Http\Response
     */
    public function show(UnitType $unitType)
    {
        return response()->json($unitType->load('parent','children'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param UnitType $unitType
     * @return \Illuminate\Http\Response
     */
    public function edit(UnitType $unitType)
    {
        return response()->json([
            'unitType' => $unitType->load('parent', 'children'),
            'unitTypes' => UnitType::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param UnitType $unitType
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, UnitType $unitType)
    {
        $request->merge(['is_editable' => $unitType->is_editable]);

        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('unit_types', 'name', 'id', "{$unitType->id}")],
            'description' => 'nullable',
            'parent_id' => 'exists:unit_types,id',
            'is_editable' => ['required', Rule::in([$unitType->is_editable]), new IsEditableRules],
            'can_be_target' => ['required', 'boolean'],
            'can_treat' => ['required', 'boolean']
        ];

        $this->validate($request, $rules);
        if(!$request->has('parent_id'))
            $unitType->parent_id = null;
        $unitType->update($request->only(['name', 'parent_id', 'description', 'others', 'can_be_target', 'can_treat']));
        return response()->json($unitType, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UnitType $unitType
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\SecureDeleteException
     */
    public function destroy(UnitType $unitType)
    {
        $unitType->secureDelete('children','units');
        return response()->json($unitType, 200);
    }
}
