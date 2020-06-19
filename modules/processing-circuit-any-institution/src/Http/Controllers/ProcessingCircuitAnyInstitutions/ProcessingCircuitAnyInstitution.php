<?php

namespace Satis2020\ProcessingCircuitAnyInstitution\Http\Controllers\ProcessingCircuitAnyInstitutions;

use Illuminate\Validation\ValidationException;
use Exception;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\ProcessingCircuit;
class ProcessingCircuitAnyInstitution extends ApiController
{
    use ProcessingCircuit;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-processing-circuit-any-institution')->only(['update', 'edit']);
    }

    /**
     * Edit the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function edit($institutionId)
    {
        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institutionId),
            'units' =>   $this->getAllUnits($institutionId)
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
    public function update(Request $request, $institutionId){

        $collection = collect([]);

        $collection = $this->rules($request->all(), $collection, $institutionId);

        $collection->each(function ($item, $key) {
            $item['claim_object']->units()->sync($item['units_ids']);
        });

        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institutionId),
            'units' =>   $this->getAllUnits($institutionId)
        ], 200);

    }



}
