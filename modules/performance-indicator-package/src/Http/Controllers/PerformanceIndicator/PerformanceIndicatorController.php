<?php


namespace Satis2020\PerformanceIndicatorPackage\Http\Controllers\PerformanceIndicator;


use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\PerformanceIndicator;

class PerformanceIndicatorController extends ApiController
{

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
            'name' => 'required',
            'description' => 'required',
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
            'name' => 'required',
            'description' => 'required',
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