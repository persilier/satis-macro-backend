<?php

namespace Satis2020\BCIReports\Http\Controllers\GlobalReport;

use Illuminate\Http\Request;
use Satis2020\BCIReports\Traits\BCIReportsTrait;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class GlobalStateReportController
 * @package Satis2020\BCIReports\Http\Controllers\GlobalStateReport
 */
class GlobalReportController extends ApiController
{
    use BCIReportsTrait,Metadata,UemoaReports;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        /*$this->middleware('permission:bci-monthly-reports')->only(['index']);

        if ($this->checkIfStaffIsPilot($this->staff())){
            $this->middleware('permission:active.pilot')->only(['index']);
        }*/
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {

        if ($request->isNotFilled('year')){
            $request->merge(['year'=>date('Y')]);
        }

        $this->validate($request, [
            'year' => 'required|date_format:Y',
        ]);

        return response()->json($this->getGlobalReportsByMonths($this->institution()->id,$request->year));

    }



}
