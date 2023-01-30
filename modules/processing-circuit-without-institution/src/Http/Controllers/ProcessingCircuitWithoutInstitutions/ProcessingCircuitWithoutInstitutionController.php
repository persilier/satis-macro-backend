<?php

namespace Satis2020\ProcessingCircuitWithoutInstitution\Http\Controllers\ProcessingCircuitWithoutInstitutions;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ProcessingCircuit;

/**
 * Class ProcessingCircuitWithoutInstitutionController
 * @package Satis2020\ProcessingCircuitWithoutInstitution\Http\Controllers\ProcessingCircuitWithoutInstitutions
 */
class ProcessingCircuitWithoutInstitutionController extends ApiController
{
    use ProcessingCircuit;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-processing-circuit-without-institution')->only(['update', 'edit']);

        $this->activityLogService = $activityLogService;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request){

        $collection = collect([]);

        $collection = $this->rules($request->all(), $collection);

        $this->detachAttachUnits($collection);

        $this->activityLogService->store("Mise à jour des circuits de traitements.",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'circuit',
            $this->user()
        );

        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits(),
            'units' =>   $this->getAllUnits()
        ], 200);

    }



}
