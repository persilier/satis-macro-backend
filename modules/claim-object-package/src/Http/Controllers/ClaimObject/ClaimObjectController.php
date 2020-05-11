<?php

namespace Satis2020\ClaimObjectPackage\Http\Controllers\ClaimObject;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\SeverityLevel;
use Satis2020\ClaimObjectPackage\Http\Resources\ClaimObject as ClaimObjectResource;
use Satis2020\ClaimObjectPackage\Http\Resources\ClaimObjectCollection;
class ClaimObjectController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return ClaimObjectCollection
     */
    public function index()
    {
        return new ClaimObjectCollection(ClaimObject::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'claim_categories' => ClaimCategory::all(),
            'severity_levels' => SeverityLevel::all()
        ];
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return ClaimObjectResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'claim_category_id' => 'required|exists:claim_categories,id',
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'integer',
            'others' => 'array'
        ];

        $this->validate($request, $rules);
        if (!$request->has('severity_levels_id')) {
            $claimCategory = ClaimCategory::find($request->claim_category_id);
            if(empty($claimCategory->severityLevel))
                return $this->errorResponse('Aucun niveau de gravité n\'est renseigné pour l\'objet ainsi pour la catégorie séléctionnée aussi.', 400);

        }

        $claimObject = ClaimObject::create($request->all());
        return new ClaimObjectResource($claimObject);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimObject  $claimObject
     * @return ClaimObjectResource
     */
    public function show(ClaimObject $claimObject)
    {
        return new ClaimObjectResource($claimObject);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimObject  $claimObject
     * @return ClaimObjectResource
     */
    public function edit(ClaimObject $claimObject)
    {
        $data = [
            'claim_object' => $claimObject->load('claimCategory', 'severityLevel'),
            'claim_categories' => ClaimCategory::all(),
            'severity_levels' => SeverityLevel::all()
        ];
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\ClaimObject $claimObject
     * @return ClaimObjectResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ClaimObject $claimObject)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'claim_category_id' => 'required|exists:claim_categories,id',
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'integer',
            'others' => 'array'
        ];

        $this->validate($request, $rules);
        if (!$request->has('severity_levels_id')) {
            $claimCategory = ClaimCategory::find($request->claim_category_id);
            if(empty($claimCategory->severityLevel))
                return $this->errorResponse('Aucun niveau de gravité n\'est renseigné pour l\'objet ainsi pour la catégorie séléctionnée aussi.', 400);

        }

        $claimObject->update($request->only(['name', 'description', 'severity_levels_id', 'time_limit', 'claim_category_id', 'others']));

        return new ClaimObjectResource($claimObject);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\ClaimObject $claimObject
     * @return ClaimObjectResource
     * @throws \Exception
     */
    public function destroy(ClaimObject $claimObject)
    {
        $claimObject->delete();
        return new ClaimObjectResource($claimObject);
    }
}
