<?php

namespace Satis2020\MyInstitutionUnit\Http\Controllers\Unit;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UnitTrait;

class UnitController extends ApiController
{
    use UnitTrait, SecureDelete;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-my-unit')->only(['index']);
        $this->middleware('permission:store-my-unit')->only(['create','store']);
        $this->middleware('permission:show-my-unit')->only(['show']);
        $this->middleware('permission:update-my-unit')->only(['edit','update']);
        $this->middleware('permission:destroy-my-unit')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json($this->getAllUnitByInstitution($this->institution()->id), 200);
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
            'parent' => $this->getAllUnitByInstitution($this->institution()->id)
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
            'parent_id' => [Rule::exists('units', 'id')->where(function ($query){
                $query->where('institution_id', $this->institution()->id);
            })],
        ];

        $this->validate($request, $rules);

        $unit = Unit::create([
            'name'=> $request->name,
            'description'=> $request->description,
            'unit_type_id'=> $request->unit_type_id,
            'parent_id'=> $request->parent_id,
            'institution_id'=> $this->institution()->id,
        ]);
        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $unit
     * @return \Illuminate\Http\Response
     */
    public function show($unit)
    {
        $unit = $this->getOneUnitByInstitution($this->institution()->id, $unit);
        return response()->json($unit, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $unit
     * @return \Illuminate\Http\Response
     */
    public function edit($unit)
    {
        return response()->json([
            'unit' => $this->getOneUnitByInstitution($this->institution()->id, $unit),
            'load' => Staff::where('institution_id',$this->institution()->id)->where('unit_id',$unit)->get(),
            'parent' => $this->getAllUnitByInstitution($this->institution()->id)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $unit
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $unit)
    {
        $unit = $this->getOneUnitByInstitution($this->institution()->id, $unit);
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'unit_type_id' => 'required|exists:unit_types,id',
            'lead_id' => [Rule::exists('staff', 'id')->where(function ($query) use ($unit) {
                $query->where('institution_id', $this->institution()->id)->where('unit_id', $unit->id);
            })],
            'parent_id' => [Rule::exists('units', 'id')->where(function ($query){
                $query->where('institution_id', $this->institution()->id);
            })],
        ];

        $this->validate($request, $rules);

        if(!$request->has('parent_id'))
            $unit->parent_id = null;
        if(!$request->has('lead_id'))
            $unit->lead_id = null;

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
    public function destroy($unit)
    {
        $unit = $this->getOneUnitByInstitution($this->institution()->id, $unit);
        $unit->secureDelete('staffs', 'children');
        return response()->json($unit, 200);
    }
}
