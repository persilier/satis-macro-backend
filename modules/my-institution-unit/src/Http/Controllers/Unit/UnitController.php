<?php

namespace Satis2020\MyInstitutionUnit\Http\Controllers\Unit;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\DataUserNature;

class UnitController extends ApiController
{
    use DataUserNature;
    protected $user;
    protected $institution;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('permission:list-my-unit')->only(['index']);
        $this->middleware('permission:create-my-unit')->only(['store']);
        $this->middleware('permission:show-my-unit')->only(['show']);
        $this->middleware('permission:update-my-unit')->only(['update']);
        $this->middleware('permission:delete-my-unit')->only(['destroy']);
        $this->user = Auth::user();
        $this->institution = $this->getInstitution($this->user->identite->staff->institution_id);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Unit::with(['unitType', 'institution', 'parent', 'lead'])->get(), 200);
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
            'load' => Staff::where('institution_id', $this->institution)->get(),
            'parent' => Unit::where('institution_id', $this->institution)->get()
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
            'lead_id' => 'exists:staff,id',
            'parent_id' => 'exists:units,id'
        ];

        $this->validate($request, $rules);

        $unit = Unit::create([
            'name'=> $request->name,
            'description'=> $request->description,
            'unit_type_id'=> $request->unit_type_id,
            'lead_id'=> $request->lead_id,
            'parent_id'=> $request->parent_id,
            'institution_id'=> $this->institution,
        ]);
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
            'unit' => $unit->load('unitType', 'institution'),
            'load' => Staff::where('institution_id', $this->institution)->get(),
            'parent' => Unit::where('institution_id', $this->institution)->get()
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
            'lead_id' => 'exists:staff,id',
            'parent_id' => 'exists:units,id'
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
