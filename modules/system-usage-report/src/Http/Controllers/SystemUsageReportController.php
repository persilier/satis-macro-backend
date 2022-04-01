<?php

namespace Satis2020\SystemUsageReport\Http\Controllers\SystemUsageReportController;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\SystemUsageReportRequest;

class SystemUsageReportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
//        $this->middleware('auth');
//        $this->middleware('permission:store-staff')->only(['store']);
    }

    public function index(SystemUsageReportRequest $request)
    {



    }
}
