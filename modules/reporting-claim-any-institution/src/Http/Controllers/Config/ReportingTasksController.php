<?php

namespace Satis2020\ReportingClaimAnyInstitution\Http\Controllers\ReportingTasks;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ReportingClaim;


class ReportingTasksController extends ApiController
{
    use ReportingClaim;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-any-institution')->only(['index']);
    }


    public function index(Request $request)
    {


    }


    public function store(Request $request)
    {
        $this->validate($request, $this->rulesTasksConfig());

        $days = $request->data['days'];
        $weeks = $request->data['weeks'];
        $months = $request->data['months'];



    }


}
