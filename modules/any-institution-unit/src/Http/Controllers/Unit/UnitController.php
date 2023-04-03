<?php

namespace Satis2020\AnyInstitutionUnit\Http\Controllers\Unit;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Traits\UnitTrait;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Rules\StateExistRule;
use Satis2020\ServicePackage\Services\CountryService;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityByInstitutionRules;

class UnitController extends ApiController
{
    use UnitTrait;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-any-unit')->only(['index']);
        $this->middleware('permission:store-any-unit')->only(['create', 'store']);
        $this->middleware('permission:show-any-unit')->only(['show']);
        $this->middleware('permission:update-any-unit')->only(['edit', 'update']);
        $this->middleware('permission:destroy-any-unit')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Unit::with(['unitType', 'institution', 'parent', 'children', 'lead.identite', 'state.country'])->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CountryService $countryService
     * @return JsonResponse
     */
    public function create(CountryService $countryService)
    {
        return response()->json([
            'unitTypes' => UnitType::all(),
            'institutions' => Institution::all(),
            'units' => $this->getAllUnitByInstitution($this->institution()->id),
            'parents' => $this->getAllUnit(),
            'countries' => $countryService->getCountriesWithStates()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        if ($request->isNotFilled('parent_id')) {
            $request->request->remove('parent_id');
        }
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityByInstitutionRules('units', 'name', null, null, $request->institution_id)],
            'description' => 'nullable',
            'unit_type_id' => 'required|exists:unit_types,id',
            'institution_id' => 'required|exists:institutions,id',
            'parent_id' => 'sometimes|', Rule::exists('units', 'id')->where(function ($query) use ($request) {
                $query->where('institution_id', $request->institution_id);
            }),
            'state_id' => ['nullable', 'numeric', new StateExistRule]
        ];
        $this->validate($request, $rules);

        $unit = Unit::create($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'parent_id', 'others', 'state_id']));

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
        return response()->json($unit, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Unit $unit
     * @param CountryService $countryService
     * @return JsonResponse
     */
    public function edit(Unit $unit, CountryService $countryService)
    {
        return response()->json([
            'unit' => $unit->load('unitType', 'institution', 'parent', 'children', 'lead.identite', 'state.country'),
            'unitTypes' => UnitType::all(),
            'institutions' => Institution::all(),
            'parents' => $this->getAllUnit(),
            'leads' => Staff::with('identite')->where('institution_id', $unit->institution->id)
                ->where('unit_id', $unit->id)->get(),
            'units' => Unit::where('institution_id', $unit->institution->id)->get(),
            'countries' => $countryService->getCountriesWithStates()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Unit $unit
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Unit $unit)
    {

        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('units', 'name', 'id', "{$unit->id}")],
            'description' => 'nullable',
            'unit_type_id' => 'required|exists:unit_types,id',
            'institution_id' => 'required|exists:institutions,id',
            'lead_id' => 'sometimes|', Rule::exists('staff', 'id')->where(function ($query) use ($request, $unit) {
                $query->where('institution_id', $request->institution_id)->where('unit_id', $unit->id);
            }),
            'parent_id' =>  'sometimes|', Rule::exists('units', 'id')->where(function ($query) use ($request) {
                $query->where('institution_id', $request->institution_id);
            }),
            'state_id' => ['nullable', 'numeric', new StateExistRule]
        ];

        $this->validate($request, $rules);

        $this->UnitHasAnStaff($request, $unit);

        if (!$request->has('parent_id'))
            $unit->parent_id = null;
        if (!$request->has('lead_id'))
            $unit->lead_id = null;

        $unit->update($request->only(['name', 'description', 'unit_type_id', 'institution_id', 'lead_id', 'parent_id', 'others', 'state_id']));

        return response()->json($unit, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\Unit $unit
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Unit $unit)
    {
        $unit->secureDelete('staffs', 'children');
        return response()->json($unit, 200);
    }
}
