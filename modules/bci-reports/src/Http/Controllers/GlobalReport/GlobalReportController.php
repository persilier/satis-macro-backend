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
        //$this->middleware('permission:list-reporting-claim-my-institution')->only(['index', 'excelExport']);
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

        $this->validate($request, [
            'year' => 'required|date_format:Y',
        ]);

        $claims = $this->getClaimsByCategories($request, true);

        return response()->json($claims);

    }



}
