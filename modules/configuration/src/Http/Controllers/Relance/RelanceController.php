<?php

namespace Satis2020\Configuration\Http\Controllers\Relance;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Class RelanceController
 * @package Satis2020\Configuration\Http\Controllers\Relance
 */
class RelanceController extends ApiController
{
    protected $activityLogService;
    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-relance-parameters')->only(['show','update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return response()->json(["coef" => json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'coef-relance')->firstOrFail()->data)], 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'coef' => 'required|integer',
        ];

        $this->validate($request, $rules);

        $metadata = Metadata::where('name', 'coef-relance')->firstOrFail()->update(['data' => json_encode
        ($request->coef)]);

        $this->activityLogService->store('Configuration du coefficient applicable pour l\'envoie de relance',
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'metadata',
            $this->user(), $metadata
        );

        return response()->json($request->only('coef'), 200);
    }

}
