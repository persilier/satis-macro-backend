<?php

namespace Satis2020\SystemEfficiencyReportAnyInstitution\Http\Controllers;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\SystemEfficiencyReportRequest;
use Satis2020\ServicePackage\Services\Reporting\SystemEfficiencyReportService;


class SystemEfficiencyReportController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:system-any-efficiency-report')->only(['index']);
    }

    public function index(SystemEfficiencyReportRequest $request, SystemEfficiencyReportService $service)
    {
        $request->merge([
            "institution_id" => request('institution_id', $this->institution()->id)
        ]);

        $systemEfficiencyReport = $service->getReportData($request);
        return response()->json($systemEfficiencyReport, 200);
    }
}
