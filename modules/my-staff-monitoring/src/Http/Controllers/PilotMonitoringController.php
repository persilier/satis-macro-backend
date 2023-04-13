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
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Services\Monitoring\PilotMonitoringService;
use Satis2020\ServicePackage\Requests\Monitoring\MyStaffMonitoringRequest;
use Satis2020\ServicePackage\Services\Monitoring\MyStaffMonitoringService;


class PilotMonitoringController extends ApiController
{
    use ClaimAwaitingTreatment, UnitTrait, ActivePilot, DataUserNature;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:show-my-pilot-monitoring')->only(['index', 'show']);
    }

    public function index(Request $request, PilotMonitoringService $service)
    {

        if (!$this->staff()->is_active_pilot) {
            abort(Response::HTTP_FORBIDDEN, "User is not allowed");
        }

        $rules = [
            'pilot_id' => 'required',
        ];

        $this->validate($request, $rules);

        $request->merge([
            "institution_id" => $this->institution()->id
        ]);

        $pilotMonitoring = $service->MyPilotMonitoring($request);
        return response()->json($pilotMonitoring, 200);
    }

    public function show(Request $request)
    {


        if (!$this->staff()->is_active_pilot) {
            abort(Response::HTTP_FORBIDDEN, "User is not allowed");
        }

        $institution = $request->institution;


        if ($institution == null) {
            
            $pilote = User::with('identite.staff', 'roles')->whereHas('roles', function ($q) {
                $q->where('name', 'pilot');
            })->get();
        } else {
            
            $pilote = User::with('identite.staff', 'roles')
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'pilot-filial');
                })
                ->whereHas('identite.staff', function ($q) use ($institution) {
                    $q->where('institution_id', $institution);
                })
                ->get();
        }


        return response()->json([
            'pilote' => $pilote
        ], 200);
    }
}
