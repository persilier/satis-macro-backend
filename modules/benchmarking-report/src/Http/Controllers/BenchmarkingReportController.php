<?php

namespace Satis2020\BenchmarkingReport\Http\Controllers;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\BenchmarkingReportRequest;
use Satis2020\ServicePackage\Services\Reporting\BenchmarkingReportService;


class BenchmarkingReportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-benchmarking-reporting')->only(['index']);
    }

    public function index(BenchmarkingReportRequest $request, BenchmarkingReportService $service)
    {
        $request->merge([
            "institution_id" => request('institution_id', $this->institution()->id)
        ]);

        $benchmarkingReport = $service->BenchmarkingReport($request);
        return response()->json($benchmarkingReport, 200);
    }
}
