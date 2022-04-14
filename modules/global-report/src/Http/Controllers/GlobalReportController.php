<?php

namespace Satis2020\GlobalReport\Http\Controllers;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\GlobalReportRequest;
use Satis2020\ServicePackage\Services\Reporting\GlobalReportService;


class GlobalReportController extends ApiController
{
    public function index(GlobalReportRequest $request, GlobalReportService $service)
    {
        $request->merge([
            "institution_id"=>$this->institution()->id
        ]);

        $globalReport = $service->GlobalReport($request);
        return response()->json($globalReport, 200);
    }
}
