<?php

namespace Satis2020\Configuration\Http\Controllers\DelaiParameters;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ReportingClaim;

/**
 * Class QualificationController
 * @package Satis2020\Configuration\Http\Controllers\DelaiParameters
 */
class TreatmentController extends ApiController
{
    use ReportingClaim;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-delai-treatment-parameters')->only(['index']);
        $this->middleware('permission:show-delai-treatment-parameters')->only(['show']);
        $this->middleware('permission:store-delai-treatment-parameters')->only(['store']);
        $this->middleware('permission:destroy-delai-treatment-parameters')->only(['destroy']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * @return JsonResponse
     */
    public function index(){

        $parameters = $this->getAllDelaiParameters('delai-treatment-parameters');
        return response()->json($parameters, 200);
    }


    /**
     * @param $parameter
     * @return JsonResponse
     */
    protected function show($parameter){

        $parameters = $this->getOneDelaiParameters('delai-treatment-parameters', $parameter);
        return response()->json($parameters, 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    protected function store(Request $request){
        $infinite = false;
        $parameters = $this->getAllDelaiParameters('delai-treatment-parameters');
        $this->validate($request, $this->rulesParameters());

        if ($request->borne_sup === '+') {
            $infinite = true;
        }
        $this->verifiedStore($request, $parameters, $infinite);

        $data = $this->storeParameters($request, $parameters, 'delai-treatment-parameters');

        $this->activityLogService->store('Configuration des paramètres de bornes pour les délais de traitement',
            $this->institution()->id,
            'metadata',
            $this->activityLogService::CREATED,
            $this->user()
        );

        return response()->json($data, 201);
    }


    /**
     * @param $parameter
     * @return JsonResponse
     */
    protected function destroy($parameter){

        $parameter = $this->destroyDelaiParameters('delai-treatment-parameters', $parameter);

        $this->activityLogService->store('Suppression du configuration des paramètres de bornes pour les délais de traitement',
            $this->institution()->id,
            'metadata',
            $this->activityLogService::DELETED,
            $this->user()
        );

        return response()->json($parameter, 200);
    }

}
