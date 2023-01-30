<?php
namespace Satis2020\ActivePilot\Http\Controllers\ReassignmentToPilot;

use Illuminate\Http\Request;
use Satis2020\ActivePilot\Http\Controllers\ReassignmentToPilot\ReassignmentToPilotTrait;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class ReassignmentToPilotController extends ApiController
{
    use ReassignmentToPilotTrait;
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:reassignment_to_pilot')->only(['store']);

        $this->activityLogService = $activityLogService;
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules());
        $institution = $this->institution();
        $user = $this->user()->load("identite");
        if ($institution->active_pilot_id!=$this->staff()->id){
            return response()->json("Vous n'êtes pas le pilote lead", 500);
        }
        $res = $this->storeReassignment($request, $this->staff(), $user);
        if ($res==null){
            return response()->json("La réaffectation a échouée", 500);
        }
        return response()->json($res, 201);
    }


}