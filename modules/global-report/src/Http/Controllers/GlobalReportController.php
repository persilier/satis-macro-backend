<?php

namespace Satis2020\GlobalReport\Http\Controllers;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\GlobalReportRequest;
use Satis2020\ServicePackage\Services\Reporting\GlobalReportService;


class GlobalReportController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
      //  $this->middleware('permission:list-global-reporting')->only(['index']);

    }

    public function index(GlobalReportRequest $request, GlobalReportService $service)
    {
        $request->merge([
            "institution_id"=>$this->institution()->id
        ]);

        $globalReport = $service->GlobalReport($request);
        return response()->json($globalReport, 200);
    }

    public function create(){
        return response()->json($this->getAllUnitByInstitution($this->institution()->id), 200);
    }
}
