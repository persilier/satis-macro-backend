<?php

namespace Satis2020\ClaimObjectPackage\Http\Controllers\ClaimObject;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\SeverityLevel;

class ClaimObjectController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(ClaimObject::with('claimCategory', 'severityLevel')->get(), 200);
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
            'claim_category_id' => 'required|exists:claim_categories,id',
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'integer'
        ];

        $this->validate($request, $rules);

        $claimObject = ClaimObject::create($request->only(['name', 'description', 'claim_category_id', 'severity_levels_id', 'time_limit', 'others']));

        return response()->json($claimObject, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimObject  $claimObject
     * @return \Illuminate\Http\Response
     */
    public function show(ClaimObject $claimObject)
    {
        return response()->json($claimObject->load('claimCategory', 'severityLevel'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimObject  $claimObject
     * @return \Illuminate\Http\Response
     */
    public function edit(ClaimObject $claimObject)
    {
        return response()->json([
            'claimObject' => $claimObject->load('claimCategory', 'severityLevel'),
            'claimCategories' => ClaimCategory::all(),
            'severityLevels' => SeverityLevel::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\ClaimObject $claimObject
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ClaimObject $claimObject)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'claim_category_id' => 'required|exists:claim_categories,id',
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'integer'
        ];

        $this->validate($request, $rules);

        $claimObject->update($request->only(['name', 'description', 'severity_levels_id', 'time_limit', 'claim_category_id', 'others']));

        return response()->json($claimObject, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\ClaimObject $claimObject
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(ClaimObject $claimObject)
    {
        $claimObject->delete();

        return response()->json($claimObject, 200);
    }
}
