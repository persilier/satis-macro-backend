<?php

namespace Satis2020\AnyStaffMonitoring\Http\Controllers;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\UnitTrait;
use Symfony\Component\HttpFoundation\Response;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\Monitoring\PilotMonitoringService;
use Satis2020\ServicePackage\Requests\Monitoring\MyStaffMonitoringRequest;
use Satis2020\ServicePackage\Services\Monitoring\MyStaffMonitoringService;
use Satis2020\ServicePackage\Services\Monitoring\CollectorMonitoringService;


class CollecteurMonitoringController extends ApiController
{
    use ClaimAwaitingTreatment, UnitTrait, ActivePilot, DataUserNature;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:show-any-collector-monitoring')->only(['index', 'show']);
    }

    public function index(Request $request, CollectorMonitoringService $service)
    {

        $rules = [
            'collector_id' => 'required',
        ];

        $this->validate($request, $rules);

        $request->merge([
            "institution_id" => request('institution_id', $this->institution()->id)
        ]);

        $collectorMonitoring = $service->MycollectorClaims($request);
        return response()->json($collectorMonitoring, 200);
    }

    public function show()
    {

        $institution_id =  request('institution_id', $this->institution()->id);
        $agent = Claim::with('treatments')->where('institution_targeted_id', $institution_id)->whereHas('treatments', function ($q) {
            $q->whereNotNull('satisfaction_measured_by');
        })->pluck('created_by');


        $collector = User::with('identite.staff')->whereHas('identite.staff', function ($q) use ($agent) {
            $q->whereIn('id', $agent);
        })->get();



        return response()->json($collector, 200);
    }
}
