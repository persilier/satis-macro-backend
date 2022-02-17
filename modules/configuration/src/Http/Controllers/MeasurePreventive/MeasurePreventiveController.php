<?php

namespace Satis2020\Configuration\Http\Controllers\MeasurePreventive;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Class MeasurePreventiveController
 * @package Satis2020\Configuration\Http\Controllers\MeasurePreventive
 */
class MeasurePreventiveController extends ApiController
{

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-measure-preventive-parameters')->only(['show','update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return response()->json(["measure-preventive" => json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'measure-preventive')->firstOrFail()->data)], 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'measure_preventive' => ['required', Rule::in([true, false])],
        ];

        $this->validate($request, $rules);

        $metadata = Metadata::where('name', 'measure-preventive')->firstOrFail()->update(['data' => json_encode
        ($request->measure_preventive)]);

        $this->activityLogService->store('Configuration pour l\'activation/désactivation de la mesure préventive',
            $this->institution()->id,
            'metadata',
            $this->activityLogService::UPDATED,
            $this->user(), $metadata
        );

        return response()->json($request->only('measure_preventive'), 200);
    }

}
