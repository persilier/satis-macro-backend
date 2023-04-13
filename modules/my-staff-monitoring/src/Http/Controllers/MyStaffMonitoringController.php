<?php

namespace Satis2020\MyStaffMonitoring\Http\Controllers;

use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\UnitTrait;
use Symfony\Component\HttpFoundation\Response;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Monitoring\MyStaffMonitoringRequest;
use Satis2020\ServicePackage\Services\Monitoring\MyStaffMonitoringService;


class MyStaffMonitoringController extends ApiController
{
    use ClaimAwaitingTreatment,UnitTrait;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:show-my-staff-monitoring')->only(['index','show']);

    }

    public function index(MyStaffMonitoringRequest $request, MyStaffMonitoringService $service)
    {
        $staff = $this->staff();
        if (!$this->staffIsUnitLead($this->staff()))
        {
            abort(Response::HTTP_FORBIDDEN,"User is not allowed");
        }
        $request->merge([
            "institution_id"=>$this->institution()->id
        ]);
        $staffMonitoring = $service->MyStaffMonitoring($request,$staff->unit_id);
        return response()->json($staffMonitoring, 200);
    }

    public function show(){
        $staff = $this->staff();
        if (!$this->staffIsUnitLead($this->staff()))
        {
            abort(Response::HTTP_FORBIDDEN,"User is not allowed");
        }
        return response()->json([
            'staffs' => $this->getTargetedStaffFromUnit($staff->unit_id)
        ], 200);
    }

    public function create(){


        $institution = Institution::With('institutionType')->whereHas('institutionType', function($q){
            $q->where('name', '=', 'filiale');
        })->get();

        
        return response()->json([
            'institution' => $institution
        ], 200);
    }


}
