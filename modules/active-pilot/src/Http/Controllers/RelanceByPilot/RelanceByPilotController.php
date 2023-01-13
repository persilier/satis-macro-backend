<?php

namespace Satis2020\ActivePilot\Http\Controllers\RelanceByPilot;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class RelanceByPilotController  extends ApiController
{
    use RelanceByPilotTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:pilot-relance-other')->only(['store']);

        $this->activityLogService = $activityLogService;
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules());

        return response()->json($config->load("institution"), 201);
    }

}