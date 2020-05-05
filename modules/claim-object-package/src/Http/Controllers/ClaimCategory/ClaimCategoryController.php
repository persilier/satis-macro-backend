<?php

namespace Satis2020\ClaimObjectPackage\Http\Controllers\ClaimCategory;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\SeverityLevel;

class ClaimCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(ClaimCategory::with('severityLevel')->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'integer'
        ];

        $this->validate($request, $rules);

        $claimCategory = ClaimCategory::create($request->only(['name', 'description', 'severity_levels_id', 'time_limit', 'others']));

        return response()->json($claimCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimCategory  $claimCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ClaimCategory $claimCategory)
    {
        return response()->json($claimCategory->load('severityLevel'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimCategory  $claimCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ClaimCategory $claimCategory)
    {
        return response()->json([
            'claimCategory' => $claimCategory->load('severityLevel'),
            'severityLevels' => SeverityLevel::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\ClaimCategory $claimCategory
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ClaimCategory $claimCategory)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'integer'
        ];

        $this->validate($request, $rules);

        $claimCategory->update($request->only(['name', 'severity_levels_id', 'time_limit', 'description', 'others']));

        return response()->json($claimCategory, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\ClaimCategory $claimCategory
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\SecureDeleteException
     */
    public function destroy(ClaimCategory $claimCategory)
    {
        $claimCategory->secureDelete('claimObjects');

        return response()->json($claimCategory, 200);
    }
}
