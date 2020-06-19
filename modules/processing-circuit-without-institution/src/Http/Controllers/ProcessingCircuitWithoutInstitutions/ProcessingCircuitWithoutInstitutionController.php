<?php

namespace Satis2020\ProcessingCircuitWithoutInstitution\Http\Controllers\ProcessingCircuitWithoutInstitutions;

use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\ProcessingCircuit;
class ProcessingCircuitWithoutInstitutionController extends ApiController
{
    use ProcessingCircuit;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        //$this->middleware('permission:update-processing-circuit-without-institution')->only(['update', 'edit']);
    }

    /**
     * Edit the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits(),
            'units' =>   $this->getAllUnits()
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
    public function update(Request $request){

        $collection = collect([]);

        $collection = $this->rules($request->all(), $collection);

        $collection->each(function ($item, $key) {
            $item['claim_object']->units()->sync($item['units_ids']);
        });

        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits(),
            'units' =>   $this->getAllUnits()
        ], 200);

    }



}
