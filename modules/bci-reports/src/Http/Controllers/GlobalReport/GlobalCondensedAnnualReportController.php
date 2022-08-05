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
class GlobalCondensedAnnualReportController extends ApiController
{
    use BCIReportsTrait,Metadata,UemoaReports;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        /*$this->middleware('permission:bci-annual-reports')->only(['index']);

        if ($this->checkIfStaffIsPilot($this->staff())){
            $this->middleware('permission:active.pilot')->only(['index']);
        }*/
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException|\Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index(Request $request)
    {

        if ($request->isNotFilled('year')){
            $request->merge(['year'=>date('Y')]);
        }

        $this->validate($request, [
            'year' => 'required|date_format:Y',
        ]);

        $claims = $this->getCondensedAnnualReports($this->institution()->id,$request->year);

        return response()->json($claims);

    }



}
