<?php

namespace Satis2020\MyStaffMonitoring\Http\Controllers;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Traits\UnitTrait;
use Symfony\Component\HttpFoundation\Response;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\Monitoring\PilotUnitService;
use Satis2020\ServicePackage\Services\Monitoring\PilotMonitoringService;
use Satis2020\ServicePackage\Requests\Monitoring\MyStaffMonitoringRequest;
use Satis2020\ServicePackage\Services\Monitoring\MyStaffMonitoringService;


class PilotUnitController extends ApiController
{
    use ClaimAwaitingTreatment, UnitTrait, ActivePilot, DataUserNature;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:show-my-pilotUnit-monitoring')->only(['index', 'show']);
    }

    public function index(Request $request, PilotUnitService $service)
    {


        $rules = [
            'unit_id' => 'required',
        ];

        $this->validate($request, $rules);

        $request->merge([
            "institution_id" => $this->institution()->id
        ]);

        $pilotUnitMonitoring = $service->PilotUnitMonitoring($request);
        return response()->json($pilotUnitMonitoring, 200);
    }

    public function show(Request $request)
    {

        $institution = $this->institution()->id ?? null;

        if ($institution == null) {
            $unit = Unit::all();
        } else {

            $unit = Unit::Where('institution_id', $institution)->get();
        }

        return response()->json([
            'unit' => $unit
        ], 200);
    }
}
