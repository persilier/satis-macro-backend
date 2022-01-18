<?php

namespace Satis2020\Configuration\Http\Controllers\MinFusionPercent;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class MinFusionPercentController extends ApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:update-min-fusion-percent-parameters')->only(['show', 'update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'min-fusion-percent')->first()->data);
        return response()->json($parameters, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {

        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'min-fusion-percent')->first()->data);

        $rules = [
            'min_fusion_percent' => 'required|integer|min:20',
        ];

        $this->validate($request, $rules);

        $new_parameters = $request->min_fusion_percent;
        
        $metadata = Metadata::where('name', 'min-fusion-percent')->first()->update(['data'=> json_encode
        ($new_parameters)]);

        $this->activityLogService->store('Configuration du pourcentage minimum prise en compte pour la détection et la fusion des doublons',
            $this->institution()->id,
            'metadata',
            $this->activityLogService::UPDATED,
            $this->user(), $metadata
        );

        return response()->json($new_parameters, 200);
    }

}