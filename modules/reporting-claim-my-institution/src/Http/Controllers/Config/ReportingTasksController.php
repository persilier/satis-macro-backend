<?php

namespace Satis2020\ReportingClaimMyInstitution\Http\Controllers\Config;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\ReportingTask;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ReportingTasksController
 * @package Satis2020\ReportingClaimMyInstitution\Http\Controllers\ReportingTasks
 */
class ReportingTasksController extends ApiController
{
    use ReportingClaim;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-any-institution')->only(['show']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        $institution = $this->institution();
        $reporting = ReportingTask::where('institution_id', $institution->id)->get();
        return response()->json($reporting,200);
    }


    /**
     * @return JsonResponse
     */
    public function create()
    {

        $period = [
            'days' => 'Jours',
            'weeks' => 'Weeks',
            'months' => 'Mois'
        ];

        return response()->json([
            'period' => $period
        ],200);
    }


    public function store(Request $request)
    {
        $this->validate($request, $this->rulesTasksConfig(false));

        $institution = $this->institution();

        $request->merge(['institution_targeted_id' => $institution->id]);

        $this->reportingTasksExists($request, $institution);

        $reporting = ReportingTask::create($this->createFillableTasks($request, $institution));

        return response()->json($reporting,201);
    }


    /**
     * @param ReportingTask $reportingTask
     * @return JsonResponse
     */
    public function edit(ReportingTask $reportingTask)
    {

        $period = [
            'days' => 'Jours',
            'weeks' => 'Weeks',
            'months' => 'Mois'
        ];

        return response()->json([
            'period' => $period,
            'reportingTask' => $reportingTask,
        ],200);
    }


    /**
     * @param Request $request
     * @param ReportingTask $reportingTask
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, ReportingTask $reportingTask)
    {

        $this->validate($request, $this->rulesTasksConfig(false));

        $institution = $this->institution();

        $this->reportingTasksExists($request, $institution, $reportingTask->id);

        $reportingTask->update($this->createFillableTasks($request, $institution));

        return response()->json($reportingTask, 201);
    }


    /**
     * @param ReportingTask $reportingTask
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(ReportingTask $reportingTask)
    {
        $reportingTask->delete();

        return response()->json($reportingTask, 200);
    }


}
