<?php

namespace Satis2020\ProcessingCircuitMyInstitution\Http\Controllers\ProcessingCircuitMyInstitutions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\ProcessingCircuit;

/**
 * Class ProcessingCircuitMyInstitutionController
 * @package Satis2020\ProcessingCircuitMyInstitution\Http\Controllers\ProcessingCircuitMyInstitutions
 */
class ProcessingCircuitMyInstitutionController extends ApiController
{
    use ProcessingCircuit;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-processing-circuit-my-institution')->only(['update', 'edit']);
    }


    /**
     * Edit the form for creating a new resource.
     * @return JsonResponse
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
     * @param Request $request
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function update(Request $request)
    {
        $institution =  $this->institution();

        $collection = collect([]);

        $collection = $this->rules($request->all(), $collection, $institution->id);

        $this->detachAttachUnits($collection ,  $institution->id);

        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institution->id),
            'units' =>  $this->getAllUnits($institution->id)
        ], 201);

    }

}
