<?php

namespace Satis2020\Configuration\Http\Controllers\RejectUnitTransferLimitation;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class RejectUnitTransferLimitationController extends ApiController
{
    protected $activityLogService;
    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:update-reject-unit-transfer-parameters')->only(['show', 'update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'reject-unit-transfer-limitation')->first()->data);
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

        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'reject-unit-transfer-limitation')->first()->data);

        $rules = [
            'number_reject_max' => 'required|integer|min:1',
        ];

        $this->validate($request, $rules);

        $new_parameters = $request->only(['number_reject_max']);
        
        $metadata = Metadata::where('name', 'reject-unit-transfer-limitation')->first()->update(['data'=> json_encode
        ($new_parameters)]);

        $this->activityLogService->store('Configuration du nombre de limitation pour le rejet',
            $this->institution()->id,
            'metadata',
            $this->activityLogService::UPDATED,
            $this->user(), $metadata
        );

        return response()->json($new_parameters, 200);
    }

}