<?php

namespace Satis2020\SystemUsageReport\Http\Controllers;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\SystemUsageReportRequest;
use Satis2020\ServicePackage\Services\Reporting\SystemUsageReportService;


class SystemUsageReportController extends ApiController
{
    public function index(SystemUsageReportRequest $request, SystemUsageReportService $service)
    {
        $request->merge([
            "institution_id"=>$this->institution()->id
        ]);

        $systemUsageReport = $service->SystemUsageReport($request);
        return response()->json($systemUsageReport, 200);

    }
}
