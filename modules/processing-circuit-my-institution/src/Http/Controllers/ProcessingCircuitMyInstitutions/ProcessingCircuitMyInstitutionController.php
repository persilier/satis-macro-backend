<?php

namespace Satis2020\ProcessingCircuitMyInstitution\Http\Controllers\ProcessingCircuitMyInstitutions;

use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\ProcessingCircuit;
class ProcessingCircuitMyInstitutionController extends ApiController
{
    use ProcessingCircuit;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        //$this->middleware('permission:update-processing-circuit-my-institution')->only(['update', 'edit']);
    }


    /**
     * Edit the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $institution = $this->institution();
        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institution->id),
            'units' =>  $this->getAllUnits($institution->id)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws RetrieveDataUserNatureException
     */
    public function update(Request $request)
    {
        $institution =  $this->institution();

        $collection = collect([]);

        $collection = $this->rules($request->all(), $collection, $institution->id);

        $collection->each(function ($item, $key) {
            $item['claim_object']->units()->sync($item['units_ids']);
        });

        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institution->id),
            'units' =>  $this->getAllUnits($institution->id)
        ], 201);

    }

}
