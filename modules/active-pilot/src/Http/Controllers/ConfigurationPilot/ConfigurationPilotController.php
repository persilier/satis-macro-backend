<?php

namespace Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot;


use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ActivePilot;

class ConfigurationPilotController extends ApiController
{
    use ActivePilot, ConfigurationPilotTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        // $this->middleware('permission:configuration-pilot')->only(['store']);
        $this->middleware('permission:update-active-pilot')->only(['store']);

        $this->activityLogService = $activityLogService;
    }

    public function index()
    {
        return response()->json($this->nowConfiguration(), 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "many_pilot" => "required|boolean"
        ]);

        $this->validate($request, $this->ruleConfiguration($request->many_pilot));

        if ($request->many_pilot && !in_array($request->lead_pilot_id, $request->pilots)) {
            return response()->json("Veuillez sélectionner le lead parmi les pilotes actifs", 500);
        }

        $config = $this->storeConfiguration($request->many_pilot);
        if ($config) {
            $this->storeActivePilotAndLead($request);
        }

        return response()->json($config->load("institution"), 201);
    }
}
