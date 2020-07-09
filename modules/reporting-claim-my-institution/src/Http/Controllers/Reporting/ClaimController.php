<?php

namespace Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ClaimController
 * @package Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting
 */
class ClaimController extends ApiController
{
    use ReportingClaim;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-my-institution')->only(['index']);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $institution = $this->institution();

        $this->validate($request, $this->rules(false));

        $statistiques = [
            'statistiqueObject' => $this->numberClaimByObject($request, $institution->id),
            'statistiqueChannel' => $this->numberChannels($request, $institution->id),
            'statistiqueQualificationPeriod'  => $this->qualificationPeriod($request, $institution->id),
            'statistiqueTreatmentPeriod'  => $this->treatmentPeriod($request, $institution->id),
            'statistiqueGraphePeriod' => $this->numberClaimByDayOrMonthOrYear($request, $institution->id)
        ];

        return response()->json($statistiques, 200);

    }


}
