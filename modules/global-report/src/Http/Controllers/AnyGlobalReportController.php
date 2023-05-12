<?php

namespace Satis2020\GlobalReport\Http\Controllers;

use Facade\Ignition\DumpRecorder\Dump;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\UnitTrait;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\GlobalReportRequest;
use Satis2020\ServicePackage\Services\Reporting\GlobalReportService;


class AnyGlobalReportController extends ApiController
{
    use UnitTrait;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        //$this->middleware('permission:any-list-global-reporting')->only(['index', 'create']);
    }

    public function index(GlobalReportRequest $request, GlobalReportService $service)
    {
        if ($request->has('institutions')) {    
            $request->merge([
                "institutions" => $request->institutions
            ]);
        }

        $globalReport = $service->GlobalReport($request);

        return response()->json($globalReport, 200);
    }

    public function create()
    {
        return response()->json(Institution::all(), 200);
    }
}
