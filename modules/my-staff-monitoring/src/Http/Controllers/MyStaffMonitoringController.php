<?php

namespace Satis2020\MyStaffMonitoring\Http\Controllers;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Requests\Monitoring\MyStaffMonitoringRequest;
use Satis2020\ServicePackage\Services\Monitoring\MyStaffMonitoringService;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Traits\UnitTrait;


class MyStaffMonitoringController extends ApiController
{
    use ClaimAwaitingTreatment;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:show-my-staff-monitoring')->only(['index','show']);

    }

    public function index(MyStaffMonitoringRequest $request, MyStaffMonitoringService $service)
    {
        $request->merge([
            "institution_id"=>$this->institution()->id
        ]);

        $staff = $this->staff();
        $staffMonitoring = $service->MyStaffMonitoring($request,$staff->unit_id);
        return response()->json($staffMonitoring, 200);
    }

    public function show(){
        $staff = $this->staff();
        return response()->json([
            'staffs' => $this->getTargetedStaffFromUnit($staff->unit_id)
        ], 200);
    }


}
