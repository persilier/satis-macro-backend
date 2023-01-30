<?php


namespace Satis2020\PerformanceIndicatorPackage\Http\Controllers\PerformanceIndicator;


use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\PerformanceIndicator;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

class PerformanceIndicatorController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-performance-indicator')->only(['index']);
        $this->middleware('permission:show-performance-indicator')->only(['show']);
        $this->middleware('permission:store-performance-indicator')->only(['store', 'create']);
        $this->middleware('permission:update-performance-indicator')->only(['update', 'edit']);
        $this->middleware('permission:destroy-performance-indicator')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(PerformanceIndicator::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('performance_indicators', 'name')],
            'description' => 'nullable',
            'value' => 'required|integer',
            'mesure_unit' => 'required'
        ];

        $this->validate($request, $rules);

        $performanceIndicator = PerformanceIndicator::create($request->only(['name', 'description', 'value', 'mesure_unit', 'others']));

        return response()->json($performanceIndicator, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param PerformanceIndicator $performanceIndicator
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(PerformanceIndicator $performanceIndicator)
    {
        return response()->json($performanceIndicator, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param PerformanceIndicator $performanceIndicator
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, PerformanceIndicator $performanceIndicator)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('performance_indicators', 'name', 'id', "{$performanceIndicator->id}")],
            'description' => 'nullable',
            'value' => 'required|integer',
            'mesure_unit' => 'required'
        ];

        $this->validate($request, $rules);

        $performanceIndicator->update($request->only(['name', 'description', 'value', 'mesure_unit', 'others']));

        return response()->json($performanceIndicator, 201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PerformanceIndicator $performanceIndicator
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(PerformanceIndicator $performanceIndicator)
    {
        $performanceIndicator->delete();

        return response()->json($performanceIndicator, 200);
    }

}