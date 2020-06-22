<?php

namespace Satis2020\ProcessingCircuitAnyInstitution\Http\Controllers\ProcessingCircuitAnyInstitutions;

use Illuminate\Validation\ValidationException;
use Exception;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ProcessingCircuit;
class ProcessingCircuitAnyInstitutionController extends ApiController
{
    use ProcessingCircuit;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
       // $this->middleware('permission:update-processing-circuit-any-institution')->only(['update', 'edit']);
    }

    /**
     * Edit the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $institution = $this->institution();
        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institution->id),
            'units' =>   $this->getAllUnits($institution->id),
            'institutions' => Institution::all(),
            'institution_id' => $institution->id
        ], 200);
    }

    /**
     * Edit the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function edit($institutionId)
    {
        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institutionId),
            'units' =>   $this->getAllUnits($institutionId),
            'institutions' => Institution::all()
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

        $this->detachAttachUnits($collection , $institutionId);

        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institutionId),
            'units' =>   $this->getAllUnits($institutionId),
            'institutions' => Institution::all()
        ], 200);

    }

}
