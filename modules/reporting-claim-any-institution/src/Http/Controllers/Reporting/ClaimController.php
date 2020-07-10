<?php

namespace Satis2020\ReportingClaimAnyInstitution\Http\Controllers\Reporting;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ClaimController
 * @package Satis2020\ReportingClaimAnyInstitution\Http\Controllers\Reporting
 */
class ClaimController extends ApiController
{
    use ReportingClaim;
    public function __construct()
    {
        parent::__construct();

        //$this->middleware('auth:api');
        //$this->middleware('permission:list-reporting-claim-any-institution')->only(['index']);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $institutionId = false;

        $this->validate($request, $this->rules());

        if((!$request->has('date_start')) || (!$request->has('date_end'))){

            $request->merge(['date_start' => now()->startOfYear(), 'date_end' =>now()->endOfYear()]);
        }

        if($request->has('institution_id')){
            $institutionId = $request->institution_id;
        }

        $statistiques = [
            'statistiqueObject' => $this->numberClaimByObject($request, $institutionId),
            'statistiqueChannel' => $this->numberChannels($request, $institutionId),
            'statistiqueQualificationPeriod'  => $this->qualificationPeriod($request, $institutionId),
            'statistiqueTreatmentPeriod'  => $this->treatmentPeriod($request, $institutionId),
            'statistiqueGraphePeriod' => $this->numberClaimByDayOrMonthOrYear($request, $institutionId),
            'institutions' => Institution::all()
        ];

        return response()->json($statistiques, 200);

    }


}
