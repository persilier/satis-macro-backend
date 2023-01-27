<?php

namespace Satis2020\ProcessingCircuitAnyInstitution\Http\Controllers\ProcessingCircuitAnyInstitutions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Exception;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ProcessingCircuit;

/**
 * Class ProcessingCircuitAnyInstitutionController
 * @package Satis2020\ProcessingCircuitAnyInstitution\Http\Controllers\ProcessingCircuitAnyInstitutions
 */
class ProcessingCircuitAnyInstitutionController extends ApiController
{
    use ProcessingCircuit;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-processing-circuit-any-institution')->only(['update', 'edit']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Edit the form for creating a new resource.
     * @return Response
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
     * @param $institutionId
     * @return Response
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
     * @param Request $request
     * @param $institutionId
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function update(Request $request, $institutionId){

        $collection = collect([]);

        $collection = $this->rules($request->all(), $collection, "$institutionId");

        $this->detachAttachUnits($collection , $institutionId);

        $this->activityLogService->store("Mise à jour des circuits de traitements.",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'circuit',
            $this->user()
        );

        return response()->json([
            'claimCategories' => $this->getAllProcessingCircuits($institutionId),
            'units' =>   $this->getAllUnits($institutionId),
            'institutions' => Institution::all()
        ], 200);

    }

}
