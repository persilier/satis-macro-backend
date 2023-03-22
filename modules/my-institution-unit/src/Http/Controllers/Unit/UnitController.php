<?php

namespace Satis2020\MyInstitutionUnit\Http\Controllers\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Satis\CountriesPackage\Models\Country;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Rules\StateExistRule;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Satis2020\ServicePackage\Services\CountryService;
use Satis2020\ServicePackage\Services\StateService;
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
        $this->middleware('permission:store-my-unit')->only(['create', 'store']);
        $this->middleware('permission:show-my-unit')->only(['show']);
        $this->middleware('permission:update-my-unit')->only(['edit', 'update']);
        $this->middleware('permission:destroy-my-unit')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        return response()->json($this->getAllUnitByInstitution($this->institution()->id), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CountryService $countryService
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function create(CountryService $countryService)
    {
        return response()->json([
            'unitTypes' => UnitType::all(),
            'units' => $this->getAllUnitByInstitution($this->institution()->id),
            'parents' => $this->getAllUnitByInstitution($this->institution()->id),
            'countries' => Country::query()
                ->where('region', 'Africa')
                ->with('states')
                ->get()
        ], 200);

        

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {

        if ($request->isNotFilled('parent_id')) {
            $request->request->remove('parent_id');
        }

        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('units', 'name')],
            'description' => 'nullable',
            'unit_type_id' => 'required|exists:unit_types,id',
            'parent_id' => ['sometimes', Rule::exists('units', 'id')->where(function ($query) {
                $query->where('institution_id', $this->institution()->id);
            })],
            'state_id' => ['nullable', 'numeric', 'exists:states,id']
        ];

        $this->validate($request, $rules);

        $unit = Unit::create([
            'name' => $request->name,
            'description' => $request->description,
            'unit_type_id' => $request->unit_type_id,
            'parent_id' => $request->parent_id,
            'institution_id' => $this->institution()->id,
            'state_id' => $request->state_id
        ]);
        return response()->json($unit, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $unit
     * @return Response
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
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
     * @param CountryService $countryService
     * @param StateService $stateService
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function edit($unit, CountryService $countryService, StateService $stateService)
    {

        return response()->json([
            'unit' => $this->getOneUnitByInstitution($this->institution()->id, $unit),
            'unitTypes' => UnitType::all(),
            'units' => $this->getAllUnitByInstitution($this->institution()->id),
            'leads' => Staff::with('identite')->where('institution_id', $this->institution()->id)->where('unit_id', $unit)->get(),
            'parents' => $this->getAllUnitByInstitution($this->institution()->id),
            'countries' => Country::query()
                ->where('region', 'Africa')
                ->with('states')
                ->get()
        ], 200);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $unit
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function update(Request $request, $unit)
    {
        if ($request->isNotFilled('parent_id')) {
            $request->request->remove('parent_id');
        }

        $unit = $this->getOneUnitByInstitution($this->institution()->id, $unit);
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('units', 'name', 'id', "{$unit->id}")],
            'description' => 'nullable',
            'unit_type_id' => 'required|exists:unit_types,id',
            'lead_id' => 'sometimes|', Rule::exists('staff', 'id')->where(function ($query) use ($unit) {
                $query->where('institution_id', $this->institution()->id)->where('unit_id', $unit->id);
            }),
            'parent_id' => ['sometimes', Rule::exists('units', 'id')->where(function ($query) {
                $query->where('institution_id', $this->institution()->id);
            })],
            'state_id' => ['nullable', 'numeric', new StateExistRule]
        ];

        $this->validate($request, $rules);

        if (!$request->has('parent_id'))
            $unit->parent_id = null;
        if (!$request->has('lead_id'))
            $unit->lead_id = null;

        $unit->update($request->only(['name', 'description', 'unit_type_id', 'lead_id', 'parent_id', 'others', 'state_id']));

        return response()->json($unit, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Unit $unit
     * @return Response
     * @throws \Exception
     */
    public function destroy($unit)
    {
        $unit = $this->getOneUnitByInstitution($this->institution()->id, $unit);
        $unit->secureDelete('staffs', 'children');
        return response()->json($unit, 200);
    }
}
