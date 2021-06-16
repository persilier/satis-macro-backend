<?php

namespace Satis2020\SeverityLevel\Http\Controllers\SeverityLevels;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\SeverityLevel;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

/**
 * Class SeverityLevelController
 * @package Satis2020\SeverityLevel\Http\Controllers\SeverityLevels
 */
class SeverityLevelController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-severity-level')->only(['index']);
        //$this->middleware('permission:store-severity-level')->only(['store']);
        $this->middleware('permission:update-severity-level')->only(['update']);
        $this->middleware('permission:show-severity-level')->only(['show']);
        //$this->middleware('permission:destroy-severity-level')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(SeverityLevel::all(), 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('severity_levels', 'name')],
            'description' => 'nullable',
            'color' => 'required|string',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        $severityLevel = SeverityLevel::create($request->only(['name', 'description', 'color', 'others']));
        return response()->json($severityLevel, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param SeverityLevel $severityLevel
     * @return JsonResponse
     */
    public function show(SeverityLevel $severityLevel)
    {
        return response()->json($severityLevel, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SeverityLevel $severityLevel
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, SeverityLevel $severityLevel)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('severity_levels', 'name', 'id', "{$severityLevel->id}")],
            'description' => 'nullable',
            'color' => 'required|string',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        $severityLevel->update($request->only(['name', 'description', 'color', 'others']));
        return response()->json($severityLevel, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SeverityLevel $severityLevel
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(SeverityLevel $severityLevel)
    {
        $severityLevel->secureDelete('claimObjects');
        return response()->json($severityLevel, 201);
    }
}
