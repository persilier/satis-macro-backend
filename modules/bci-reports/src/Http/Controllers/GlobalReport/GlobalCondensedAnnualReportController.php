<?php

namespace Satis2020\BCIReports\Http\Controllers\GlobalReport;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\BCIReports\Traits\BCIReportsTrait;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class GlobalStateReportController
 * @package Satis2020\BCIReports\Http\Controllers\GlobalStateReport
 */
class GlobalCondensedAnnualReportController extends ApiController
{
    use BCIReportsTrait, Metadata, UemoaReports;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:bci-annual-reports')->only(['index']);

        if ($this->checkIfStaffIsPilot($this->staff())){
            $this->allowOnlyActivePilot($this->staff());
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|RetrieveDataUserNatureException
     */
    public function index(Request $request)
    {

        if ($request->isNotFilled('year')){
            $request->merge(['year'=>date('Y')]);
        }
        if ($request->isNotFilled('timelimit')){
            $request->merge(['timelimit'=>45]);
        }

        $this->validate($request, [
            'year' => 'required|date_format:Y',
        ]);

        return response()->json( $this->getCondensedAnnualReports($this->institution()->id,$request));
    }
    
}
