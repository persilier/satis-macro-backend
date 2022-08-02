<?php

namespace Satis2020\ReportingClaimAnyInstitution\Http\Controllers\Reporting;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-any-institution')->only(['index']);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $institution = false;

        $this->validate($request, $this->rules());

        if((!$request->has('date_start')) || (!$request->has('date_end'))){

            $request->merge(['date_start' => now()->startOfMonth()->subMonths(11), 'date_end' => now()->endOfMonth()]);

        }

        if($request->has('institution_id')){

            $institution = true;

        }

        $statistiques = [
            'statistiqueObject' => $this->statistiqueObjectsClaims($request, $institution),
            'statistiqueChannel' => $this->statistiqueChannels($request, $institution),
            'statistiqueQualificationPeriod' => $this->statistiqueQualifications($request, $institution),
            'statistiqueTreatmentPeriod' => $this->statistiqueTreatments($request, $institution),
            'statistiqueGraphePeriod' => $this->statistiqueEvolutions($request, $institution),
            'institutions' => Institution::all()
        ];

        return response()->json($statistiques, 200);

    }


}
