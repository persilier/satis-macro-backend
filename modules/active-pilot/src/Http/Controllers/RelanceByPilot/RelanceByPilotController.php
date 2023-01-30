<?php

namespace Satis2020\ActivePilot\Http\Controllers\RelanceByPilot;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\DataUserNature;

class RelanceByPilotController  extends ApiController
{
    use RelanceByPilotTrait, DataUserNature;

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
        $staff = $this->staff();
        $res = $this->storeRelance($request, $staff);
        if ($res==null){
            return response()->json("L'envoie de la relance a échoué", 500);
        }
        return response()->json($res, 201);
    }

}