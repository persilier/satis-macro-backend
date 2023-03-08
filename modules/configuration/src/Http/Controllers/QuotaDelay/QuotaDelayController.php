<?php

namespace Satis2020\Configuration\Http\Controllers\QuotaDelay;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class QuotaDelayController extends ApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:show-configuration-quota-delay')->only(['show']);
        $this->middleware('permission:update-configuration-quota-delay')->only(['update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $parameters = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'configuration-quota-delay')->first()->data));
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

        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'configuration-quota-delay')->first()->data);

       
        $rules = [
            'assignment_unit' => 'required|between:0,99.99',
            'assignment_staff' => 'required|between:0,99.99',
            'assignment_treatment' => 'required|between:0,99.99',
            'assignment_validation' => 'required|between:0,99.99',
            'assignment_measure_satisfaction' => 'required|between:0,99.99'
        ];

        
        $this->validate($request, $rules);

        $total = $request->assignment_unit + $request->assignment_staff + $request->assignment_treatment + $request->assignment_validation + $request->assignment_measure_satisfaction;

        if ($total != 100) {
            $this->errorResponse('Le total des pourcentages doit faire 100.', 400);
        }
        

        $new_parameters = $request->only(['assignment_unit', 'assignment_staff', 'assignment_treatment', 'assignment_validation', 'assignment_measure_satisfaction']);
        
        $metadata = Metadata::where('name', 'configuration-quota-delay')->first();
        $metadata->update(['data'=> json_encode($new_parameters)]);

        $this->activityLogService->store('configuration des quota et répartition des délai de traitement pour chaque objet de réclamation',
            $this->institution()->id,
            'metadata',
            $this->activityLogService::UPDATED,
            $this->user(), $metadata
        );

        return response()->json($new_parameters, 200);
    }

}